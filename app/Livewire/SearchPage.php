<?php

namespace App\Livewire;

use App\Http\Requests\Search\SearchRequest;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use App\Models\UserList;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SearchPage extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $type = 'all';

    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $user = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->validate(SearchRequest::rulesFor());

        $this->type = $this->normalizedType();
        $this->sort = $this->normalizedSort();
        $this->q = trim($this->q);
        $this->user = trim($this->user);
    }

    private function normalizedType(): string
    {
        return in_array($this->type, ['all', 'posts', 'users', 'tags', 'lists', 'media'], true) ? $this->type : 'all';
    }

    private function normalizedQuery(): string
    {
        return trim($this->q);
    }

    private function normalizedSort(): string
    {
        return in_array($this->sort, ['latest', 'top'], true) ? $this->sort : 'latest';
    }

    private function normalizedUsernameFilter(): ?string
    {
        $value = trim($this->user);
        $value = ltrim($value, '@');

        return $value === '' ? null : mb_strtolower($value);
    }

    private function parsedDates(): array
    {
        try {
            $from = trim($this->from) !== '' ? CarbonImmutable::parse($this->from)->startOfDay() : null;
        } catch (\Throwable) {
            $from = null;
        }

        try {
            $to = trim($this->to) !== '' ? CarbonImmutable::parse($this->to)->endOfDay() : null;
        } catch (\Throwable) {
            $to = null;
        }

        return [$from, $to];
    }

    /**
     * @return array{
     *   from_user: ?string,
     *   to_user: ?string,
     *   since: ?\Carbon\CarbonImmutable,
     *   until: ?\Carbon\CarbonImmutable,
     *   min_likes: ?int,
     *   min_reposts: ?int,
     *   has_images: bool,
     *   has_videos: bool,
     *   has_media: bool,
     *   has_links: bool,
     *   verified_only: bool,
     *   sort: ?string,
     *   groups: array<int, array<int, string>>,
     *   terms: array<int, string>,
     *   phrases: array<int, string>,
     *   exclude: array<int, string>,
     *   tags: array<int, string>,
     *   mentions: array<int, string>,
     * }
     */
    private function parseOperators(string $raw): array
    {
        $q = trim($raw);
        $tokens = [];
        if ($q !== '') {
            preg_match_all('/-?"[^"]+"|\\S+/', $q, $m);
            $tokens = $m[0] ?? [];
        }

        $fromUser = null;
        $toUser = null;
        $since = null;
        $until = null;
        $minLikes = null;
        $minReposts = null;
        $hasImages = false;
        $hasVideos = false;
        $hasMedia = false;
        $hasLinks = false;
        $verifiedOnly = false;
        $sort = null;

        $groups = [[]];
        $terms = [];
        $phrases = [];
        $exclude = [];
        $tags = [];
        $mentions = [];

        foreach ($tokens as $token) {
            $negated = str_starts_with($token, '-');
            $t = $negated ? substr($token, 1) : $token;

            if ($t === '') {
                continue;
            }

            if (! $negated && preg_match('/^or$/i', $t)) {
                if (count($groups[array_key_last($groups)]) > 0) {
                    $groups[] = [];
                }

                continue;
            }

            $isPhrase = str_starts_with($t, '"') && str_ends_with($t, '"') && mb_strlen($t) >= 2;
            $value = $t;
            if ($isPhrase) {
                $value = trim(mb_substr($t, 1, mb_strlen($t) - 2));

                if ($value === '') {
                    continue;
                }
            }

            if (! $isPhrase && ! $negated) {
                if (preg_match('/^from:([A-Za-z0-9_]{1,30})$/i', $t, $m)) {
                    $fromUser = mb_strtolower($m[1]);
                    continue;
                }

                if (preg_match('/^to:([A-Za-z0-9_]{1,30})$/i', $t, $m)) {
                    $toUser = mb_strtolower($m[1]);
                    continue;
                }

                if (preg_match('/^since:(\d{4}-\d{2}-\d{2})$/', $t, $m)) {
                    try {
                        $since = CarbonImmutable::parse($m[1])->startOfDay();
                    } catch (\Throwable) {
                        $since = null;
                    }
                    continue;
                }

                if (preg_match('/^until:(\d{4}-\d{2}-\d{2})$/', $t, $m)) {
                    try {
                        $until = CarbonImmutable::parse($m[1])->endOfDay();
                    } catch (\Throwable) {
                        $until = null;
                    }
                    continue;
                }

                if (preg_match('/^min_(?:likes|faves):(\d+)$/', $t, $m)) {
                    $minLikes = (int) $m[1];
                    continue;
                }

                if (preg_match('/^min_(?:retweets|reposts):(\d+)$/', $t, $m)) {
                    $minReposts = (int) $m[1];
                    continue;
                }

                if (preg_match('/^filter:verified$/i', $t)) {
                    $verifiedOnly = true;
                    continue;
                }

                if (preg_match('/^has:images$/i', $t)) {
                    $hasImages = true;
                    continue;
                }

                if (preg_match('/^has:(?:videos|video)$/i', $t)) {
                    $hasVideos = true;
                    continue;
                }

                if (preg_match('/^has:media$/i', $t)) {
                    $hasMedia = true;
                    continue;
                }

                if (preg_match('/^has:links$/i', $t)) {
                    $hasLinks = true;
                    continue;
                }

                if (preg_match('/^sort:(latest|top)$/i', $t, $m)) {
                    $sort = mb_strtolower($m[1]);
                    continue;
                }
            }

            if (! $isPhrase && str_starts_with($t, '#')) {
                $tag = mb_strtolower(ltrim($t, '#'));
                if ($tag !== '') {
                    if ($negated) {
                        $exclude[] = '#'.$tag;
                    } else {
                        $tags[] = $tag;
                    }
                }
                continue;
            }

            if (! $isPhrase && str_starts_with($t, '@')) {
                $mention = mb_strtolower(ltrim($t, '@'));
                if ($mention !== '') {
                    if ($negated) {
                        $exclude[] = '@'.$mention;
                    } else {
                        $mentions[] = $mention;
                    }
                }
                continue;
            }

            if ($negated) {
                $exclude[] = $value;
                continue;
            }

            $groups[array_key_last($groups)][] = $value;

            if ($isPhrase) {
                $phrases[] = $value;
            } else {
                $terms[] = $value;
            }
        }

        $groups = array_values(array_filter($groups, fn (array $group) => count($group) > 0));

        return [
            'from_user' => $fromUser,
            'to_user' => $toUser,
            'since' => $since,
            'until' => $until,
            'min_likes' => $minLikes,
            'min_reposts' => $minReposts,
            'has_images' => $hasImages,
            'has_videos' => $hasVideos,
            'has_media' => $hasMedia,
            'has_links' => $hasLinks,
            'verified_only' => $verifiedOnly,
            'sort' => $sort,
            'groups' => $groups,
            'terms' => $terms,
            'phrases' => $phrases,
            'exclude' => $exclude,
            'tags' => array_values(array_unique($tags)),
            'mentions' => array_values(array_unique($mentions)),
        ];
    }

    public function getTrendingHashtagsProperty()
    {
        $since = now()->subDay();

        return Hashtag::query()
            ->select(['hashtags.*'])
            ->selectRaw('count(*) as uses_count')
            ->join('hashtag_post', 'hashtag_post.hashtag_id', '=', 'hashtags.id')
            ->join('posts', 'posts.id', '=', 'hashtag_post.post_id')
            ->whereNull('posts.reply_to_id')
            ->where('posts.created_at', '>=', $since)
            ->groupBy('hashtags.id')
            ->orderByDesc('uses_count')
            ->limit(10)
            ->get();
    }

    public function getUsersProperty()
    {
        if (! in_array($this->normalizedType(), ['all', 'users'], true)) {
            return collect();
        }

        $q = ltrim($this->normalizedQuery(), '@');
        if ($q === '') {
            $query = User::query()->latest()->limit(10);

            if (Auth::check()) {
                $exclude = Auth::user()->excludedUserIds();
                if ($exclude->isNotEmpty()) {
                    $query->whereNotIn('id', $exclude);
                }
            }

            return $query->get();
        }

        $needle = '%'.mb_strtolower($q).'%';

        $query = User::query()
            ->where(function (Builder $query) use ($needle): void {
                $query
                    ->whereRaw('lower(username) like ?', [$needle])
                    ->orWhereRaw('lower(name) like ?', [$needle]);
            })
            ->limit(20);

        if (Auth::check()) {
            $exclude = Auth::user()->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('id', $exclude);
            }
        }

        return $query->get();
    }

    public function getHashtagsProperty()
    {
        if (! in_array($this->normalizedType(), ['all', 'tags'], true)) {
            return collect();
        }

        $q = $this->normalizedQuery();
        if ($q === '') {
            return collect();
        }

        $tag = ltrim($q, '#');
        $tag = mb_strtolower($tag);

        return Hashtag::query()
            ->where('tag', 'like', '%'.$tag.'%')
            ->orderBy('tag')
            ->limit(20)
            ->get();
    }

    public function getListsProperty()
    {
        $normalizedType = $this->normalizedType();

        if (! in_array($normalizedType, ['all', 'lists'], true)) {
            return collect();
        }

        $q = trim($this->normalizedQuery());
        if ($q === '' && $normalizedType !== 'lists') {
            return collect();
        }

        $query = UserList::query()
            ->where('is_private', false)
            ->with('owner')
            ->withCount(['members', 'subscribers']);

        if (Auth::check()) {
            $exclude = Auth::user()->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('owner_id', $exclude);
            }
        }

        if ($q !== '') {
            $needle = '%'.mb_strtolower(ltrim($q, '@')).'%';

            $query->where(function (Builder $query) use ($needle): void {
                $query
                    ->whereRaw('lower(user_lists.name) like ?', [$needle])
                    ->orWhereRaw('lower(coalesce(user_lists.description, \'\')) like ?', [$needle])
                    ->orWhereHas('owner', fn (Builder $q) => $q->whereRaw('lower(username) like ?', [$needle]));
            });
        }

        if ($this->normalizedSort() === 'top') {
            return $query
                ->orderByDesc('subscribers_count')
                ->orderByDesc('members_count')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        }

        return $query
            ->latest()
            ->limit(20)
            ->get();
    }

    public function getPostsProperty()
    {
        $normalizedType = $this->normalizedType();

        if (! in_array($normalizedType, ['all', 'posts', 'media'], true)) {
            return null;
        }

        $ops = $this->parseOperators($this->normalizedQuery());

        [$from, $to] = $this->parsedDates();
        $from = $from ?? $ops['since'];
        $to = $to ?? $ops['until'];

        $username = $this->normalizedUsernameFilter() ?? $ops['from_user'];
        $effectiveSort = in_array($ops['sort'], ['latest', 'top'], true) ? $ops['sort'] : $this->normalizedSort();

        $query = Post::query()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->withCount(['likes', 'reposts', 'replies']);

        if (Auth::check()) {
            $exclude = Auth::user()->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }
        }

        if ($username) {
            $userId = User::query()->where('username', $username)->value('id');
            if (! $userId) {
                return $query->whereRaw('1 = 0')->paginate(15);
            }

            $query->where('user_id', $userId);
        }

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        if ($ops['verified_only']) {
            $query->whereHas('user', fn ($uq) => $uq->where('is_verified', true));
        }

        if ($normalizedType === 'media' || $ops['has_media']) {
            $query->where(function ($mq) {
                $mq->whereHas('images')->orWhereNotNull('video_path');
            });
        }

        if ($ops['has_images']) {
            $query->whereHas('images');
        }

        if ($ops['has_videos']) {
            $query->whereNotNull('video_path');
        }

        if ($ops['has_links']) {
            $query->where(function ($q) {
                $q->whereRaw('lower(body) like ?', ['%http://%'])
                    ->orWhereRaw('lower(body) like ?', ['%https://%']);
            });
        }

        if ($ops['min_likes'] !== null) {
            $query->whereRaw('(select count(*) from likes where likes.post_id = posts.id) >= ?', [$ops['min_likes']]);
        }

        if ($ops['min_reposts'] !== null) {
            $query->whereRaw('(select count(*) from posts as rp where rp.repost_of_id = posts.id and rp.reply_to_id is null) >= ?', [$ops['min_reposts']]);
        }

        if ($ops['to_user']) {
            $mentionedUserId = User::query()->where('username', $ops['to_user'])->value('id');
            if ($mentionedUserId) {
                $query->whereHas('mentions', fn ($mq) => $mq->where('mentioned_user_id', $mentionedUserId));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        foreach ($ops['mentions'] as $mention) {
            $mentionedUserId = User::query()->where('username', $mention)->value('id');
            if (! $mentionedUserId) {
                return $query->whereRaw('1 = 0')->paginate(15);
            }

            $query->whereHas('mentions', fn ($mq) => $mq->where('mentioned_user_id', $mentionedUserId));
        }

        foreach ($ops['tags'] as $tag) {
            $query->whereHas('hashtags', fn ($hq) => $hq->where('tag', $tag));
        }

        if (count($ops['groups'])) {
            $groups = $ops['groups'];

            $query->where(function ($q) use ($groups) {
                foreach ($groups as $group) {
                    $q->orWhere(function ($sq) use ($group) {
                        foreach ($group as $term) {
                            $needle = '%'.mb_strtolower($term).'%';
                            $sq->whereRaw('lower(body) like ?', [$needle]);
                        }
                    });
                }
            });
        }

        foreach ($ops['exclude'] as $term) {
            $t = trim($term);
            if ($t === '') {
                continue;
            }

            $needle = '%'.mb_strtolower($t).'%';
            $query->whereRaw('lower(body) not like ?', [$needle]);
        }

        if ($effectiveSort === 'top') {
            return $query
                ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return $query->latest()->paginate(15);
    }

    public function render()
    {
        return view('livewire.search-page')->layout('layouts.app');
    }
}
