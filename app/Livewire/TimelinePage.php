<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Space;
use App\Models\User;
use App\Services\DiscoverService;
use App\Services\FollowService;
use App\Services\TrendingService;
use App\Support\SqlHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TimelinePage extends Component
{
    use WithPagination;

    #[Url]
    public string $feed = 'following';

    public ?string $latestSeenAt = null;

    public bool $hasNewPosts = false;

    protected $listeners = [
        'post-created' => '$refresh',
    ];

    #[On('load-more')]
    public function loadMore(): void
    {
        if (! $this->posts->hasMorePages()) {
            return;
        }

        $this->nextPage();
    }

    #[On('new-post-available')]
    public function handleNewPostAvailable(): void
    {
        $this->hasNewPosts = true;
    }

    public function mount(): void
    {
        $this->feed = $this->normalizedFeed();
        $this->updateLatestSeenAt();
    }

    public function updatedFeed(): void
    {
        $normalized = $this->normalizedFeed();
        if ($this->feed !== $normalized) {
            $this->feed = $normalized;
        }
        $this->resetPage();
        $this->updateLatestSeenAt();
        $this->hasNewPosts = false;
    }

    private function normalizedFeed(): string
    {
        $normalized = in_array($this->feed, ['following', 'for-you'], true) ? $this->feed : 'following';

        if (! Auth::check()) {
            return 'for-you';
        }

        return $normalized;
    }

    private function showReplies(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return Auth::user()->timelineSetting('show_replies', false);
    }

    private function showRetweets(): bool
    {
        if (! Auth::check()) {
            return true;
        }

        return Auth::user()->timelineSetting('show_retweets', true);
    }

    private function updateLatestSeenAt(): void
    {
        $value = $this->feedQuery(false)->max('created_at');
        $this->latestSeenAt = $value ? (string) $value : now()->toDateTimeString();
    }

    private function baseQuery(bool $withRelations = true)
    {
        $query = Post::query();
        $viewer = Auth::user();

        if ($withRelations) {
            $query->withPostCardRelations($viewer, true);

            if ($this->showReplies()) {
                $query->with([
                    'replyTo:id,user_id',
                    'replyTo.user:id,username',
                ]);
            }
        }

        if (! $this->showReplies()) {
            $query->whereNull('reply_to_id')->where('is_reply_like', false);
        }

        if (! $this->showRetweets()) {
            $query->where(function ($q) {
                $q->whereNull('repost_of_id')->orWhere('body', '!=', '');
            });
        }

        if ($viewer) {
            $exclude = $viewer->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }

            $this->applyMutedTermsToPostsQuery($query, $viewer);
        }

        return $query;
    }

    private function feedQuery(bool $withRelations = true): Builder
    {
        $query = $this->baseQuery($withRelations);

        if ($this->normalizedFeed() === 'following') {
            if (Auth::check()) {
                $followingIds = Auth::user()->followingIdsWithSelf();
                $query->whereIn('user_id', $followingIds);
            }

            return $query;
        }

        return $query->where('posts.created_at', '>=', now()->subDays(7));
    }

    private function applyMutedTermsToPostsQuery(Builder $query, \App\Models\User $viewer): void
    {
        $terms = $viewer->activeMutedTerms();

        if ($terms->isEmpty()) {
            return;
        }

        $needsFollowingIds = $terms->contains(fn ($term) => (bool) $term->only_non_followed);
        $followingIds = $needsFollowingIds ? $viewer->followingIdsWithSelf()->all() : [];
        $wholeWordPostSql = SqlHelper::lowerWithPadding('posts.body');
        $wholeWordOriginalSql = SqlHelper::lowerWithPadding('original.body');

        foreach ($terms as $term) {
            $raw = trim((string) $term->term);
            if ($raw === '') {
                continue;
            }

            $needle = mb_strtolower($raw);
            if (str_starts_with($needle, '#')) {
                $needle = '#'.ltrim($needle, '#');
            }

            $matchArg = null;

            if ($term->whole_word && preg_match('/^[a-z0-9_]+$/i', $needle)) {
                $postMatchSql = $wholeWordPostSql.' like ?';
                $originalMatchSql = $wholeWordOriginalSql.' like ?';
                $matchArg = '% '.$needle.' %';
            } else {
                $postMatchSql = 'lower(posts.body) like ?';
                $originalMatchSql = 'lower(original.body) like ?';
                $matchArg = '%'.$needle.'%';
            }

            $repostMatchSql = "exists (select 1 from posts as original where original.id = posts.repost_of_id and ($originalMatchSql))";
            $combinedMatchSql = "($postMatchSql) or ($repostMatchSql)";

            if ($term->only_non_followed && count($followingIds)) {
                $query->where(function ($q) use ($followingIds, $combinedMatchSql, $matchArg): void {
                    $q->whereIn('user_id', $followingIds)->orWhereRaw('('.$combinedMatchSql.') = 0', [$matchArg, $matchArg]);
                });
            } else {
                $query->whereRaw('('.$combinedMatchSql.') = 0', [$matchArg, $matchArg]);
            }
        }
    }

    #[Computed]
    public function posts()
    {
        $query = $this->feedQuery();
        $feed = $this->normalizedFeed();

        if ($feed === 'following') {
            return $query->latest('posts.created_at')->orderByDesc('posts.id')->simplePaginate(15);
        }

        // "For You": include broader content, ranked by engagement + recency,
        // with a small bias towards followed accounts when signed in.
        if (Auth::check()) {
            $query->orderByFollowBias(Auth::user());
        }

        return $query
            ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
            ->orderByDesc('posts.created_at')
            ->orderByDesc('posts.id')
            ->simplePaginate(15);
    }

    #[Computed]
    public function liveSpaces()
    {
        if (! Auth::check()) {
            return collect();
        }

        $viewer = Auth::user();
        $cacheKey = 'timeline:live-spaces:'.$viewer->id;

        return Cache::remember($cacheKey, $this->spacesCacheTtl(), function () use ($viewer) {
            $followingIds = $viewer->followingIdsWithSelf();
            $exclude = $viewer->excludedUserIds();

            return Space::query()
                ->select(['id', 'host_user_id', 'title', 'started_at'])
                ->whereNotNull('started_at')
                ->whereNull('ended_at')
                ->whereIn('host_user_id', $followingIds)
                ->when($exclude->isNotEmpty(), fn ($q) => $q->whereNotIn('host_user_id', $exclude))
                ->with(['host:id,name,username,avatar_path'])
                ->latest('started_at')
                ->limit(8)
                ->get();
        });
    }

    #[Computed]
    public function recommendedUsers()
    {
        if (! Auth::check()) {
            return collect();
        }

        return app(DiscoverService::class)->recommendedUsers(Auth::user(), 5);
    }

    #[Computed]
    public function timelineFilters(): array
    {
        if (! Auth::check()) {
            return [];
        }

        return [
            'replies' => $this->showReplies(),
            'retweets' => $this->showRetweets(),
        ];
    }

    public function toggleFollow(int $userId): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::id() === $userId, 403);

        $target = User::query()->findOrFail($userId);
        abort_if(Auth::user()->isBlockedEitherWay($target), 403);

        app(FollowService::class)->toggle(Auth::user(), $target);

        $this->dispatch('$refresh');
    }

    #[Computed]
    public function upcomingSpaces()
    {
        if (! Auth::check()) {
            return collect();
        }

        $viewer = Auth::user();
        $cacheKey = 'timeline:upcoming-spaces:'.$viewer->id;

        return Cache::remember($cacheKey, $this->spacesCacheTtl(), function () use ($viewer) {
            $followingIds = $viewer->followingIdsWithSelf();
            $exclude = $viewer->excludedUserIds();

            return Space::query()
                ->select(['id', 'host_user_id', 'title', 'scheduled_for'])
                ->whereNull('started_at')
                ->whereNull('ended_at')
                ->whereNotNull('scheduled_for')
                ->where('scheduled_for', '>=', now())
                ->whereIn('host_user_id', $followingIds)
                ->when($exclude->isNotEmpty(), fn ($q) => $q->whereNotIn('host_user_id', $exclude))
                ->with(['host:id,name,username,avatar_path'])
                ->orderBy('scheduled_for')
                ->limit(8)
                ->get();
        });
    }

    private function spacesCacheTtl(): \DateTimeInterface
    {
        return now()->addSeconds(60);
    }

    private function normalizedViewerLocation(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        $value = trim((string) (Auth::user()->location ?? ''));

        return $value === '' ? null : mb_substr($value, 0, 60);
    }

    #[Computed]
    public function trendingHashtags()
    {
        return app(TrendingService::class)->trendingHashtags(Auth::user(), 8, $this->normalizedViewerLocation());
    }

    #[Computed]
    public function trendingKeywords()
    {
        return app(TrendingService::class)->trendingKeywords(Auth::user(), 8, $this->normalizedViewerLocation());
    }

    public function checkForNewPosts(): void
    {
        if (! Auth::check()) {
            $this->skipRender();
            return;
        }

        if ($this->hasNewPosts) {
            $this->skipRender();
            return;
        }

        if (! $this->latestSeenAt) {
            $this->updateLatestSeenAt();
            $this->skipRender();
            return;
        }

        $this->hasNewPosts = $this->feedQuery(false)
            ->where('created_at', '>', $this->latestSeenAt)
            ->exists();

        if (! $this->hasNewPosts) {
            $this->skipRender();
        }
    }

    public function refreshTimeline(): void
    {
        $this->resetPage();
        $this->updateLatestSeenAt();
        $this->hasNewPosts = false;
    }

    public function render()
    {
        return view('livewire.timeline-page')->layout('layouts.app');
    }
}
