<?php

namespace App\Livewire;

use App\Http\Requests\Search\SearchRequest;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
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
        $this->q = trim($this->q);
        $this->user = trim($this->user);
    }

    private function normalizedType(): string
    {
        return in_array($this->type, ['all', 'posts', 'users', 'tags'], true) ? $this->type : 'all';
    }

    private function normalizedQuery(): string
    {
        return trim($this->q);
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

        $q = $this->normalizedQuery();
        if ($q === '') {
            return User::query()->latest()->limit(10)->get();
        }

        $needle = '%'.mb_strtolower($q).'%';

        return User::query()
            ->where(function (Builder $query) use ($needle): void {
                $query
                    ->whereRaw('lower(username) like ?', [$needle])
                    ->orWhereRaw('lower(name) like ?', [$needle]);
            })
            ->limit(20)
            ->get();
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

    public function getPostsProperty()
    {
        if (! in_array($this->normalizedType(), ['all', 'posts'], true)) {
            return null;
        }

        [$from, $to] = $this->parsedDates();
        $username = $this->normalizedUsernameFilter();

        $query = Post::query()
            ->whereNull('reply_to_id')
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->withCount(['likes', 'reposts', 'replies']);

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

        $q = $this->normalizedQuery();
        if ($q !== '') {
            if (str_starts_with($q, '@')) {
                $mention = mb_strtolower(ltrim($q, '@'));
                $mentionedUserId = User::query()->where('username', $mention)->value('id');
                if ($mentionedUserId) {
                    $query->whereHas('mentions', fn ($mq) => $mq->where('mentioned_user_id', $mentionedUserId));
                } else {
                    $query->whereRaw('1 = 0');
                }
            } elseif (str_starts_with($q, '#')) {
                $tag = mb_strtolower(ltrim($q, '#'));
                $query->whereHas('hashtags', fn ($hq) => $hq->where('tag', $tag));
            } else {
                $needle = '%'.mb_strtolower($q).'%';
                $query->whereRaw('lower(body) like ?', [$needle]);
            }
        }

        return $query->latest()->paginate(15);
    }

    public function render()
    {
        return view('livewire.search-page')->layout('layouts.app');
    }
}
