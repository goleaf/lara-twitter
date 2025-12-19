<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Space;
use App\Models\User;
use App\Services\DiscoverService;
use App\Services\FollowService;
use App\Services\TrendingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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

    public function mount(): void
    {
        $value = $this->baseQuery()->max('created_at');
        $this->latestSeenAt = $value ? (string) $value : null;
    }

    public function updatedFeed(): void
    {
        $this->resetPage();
        $value = $this->baseQuery()->max('created_at');
        $this->latestSeenAt = $value ? (string) $value : null;
        $this->hasNewPosts = false;
    }

    private function normalizedFeed(): string
    {
        return in_array($this->feed, ['following', 'for-you'], true) ? $this->feed : 'following';
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

    private function baseQuery()
    {
        $query = Post::query()
            ->with([
                'user',
                'images',
                'replyTo.user',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts', 'replies']);

        if (! $this->showReplies()) {
            $query->whereNull('reply_to_id')->where('is_reply_like', false);
        }

        if (! $this->showRetweets()) {
            $query->where(function ($q) {
                $q->whereNull('repost_of_id')->orWhere('body', '!=', '');
            });
        }

        if (Auth::check()) {
            $viewer = Auth::user();

            $mutedIds = $viewer->mutesInitiated()->pluck('muted_id');
            $blockedIds = $viewer->blocksInitiated()->pluck('blocked_id');
            $blockedByIds = $viewer->blocksReceived()->pluck('blocker_id');

            $exclude = $mutedIds->merge($blockedIds)->merge($blockedByIds)->unique()->values();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }

            $this->applyMutedTermsToPostsQuery($query, $viewer);
        }

        return $query;
    }

    private function applyMutedTermsToPostsQuery(Builder $query, \App\Models\User $viewer): void
    {
        $terms = $viewer->mutedTerms()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where('mute_timeline', true)
            ->latest()
            ->limit(50)
            ->get();

        if ($terms->isEmpty()) {
            return;
        }

        $followingIds = $viewer->following()->pluck('users.id')->push($viewer->id)->all();

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
                $postMatchSql = "(' ' || lower(posts.body) || ' ') like ?";
                $originalMatchSql = "(' ' || lower(original.body) || ' ') like ?";
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

    public function getPostsProperty()
    {
        $query = $this->baseQuery();

        if ($this->normalizedFeed() === 'following') {
            $query->when(Auth::check(), function (Builder $query): void {
                $followingIds = Auth::user()->following()->pluck('users.id');
                $query->whereIn('user_id', $followingIds->push(Auth::id()));
            });

            return $query->latest()->paginate(15);
        }

        // "For You": include broader content, ranked by engagement + recency,
        // with a small bias towards followed accounts when signed in.
        $query->where('created_at', '>=', now()->subDays(7));

        if (Auth::check()) {
            $followingIds = Auth::user()->following()->pluck('users.id')->push(Auth::id())->all();
            $idsCsv = implode(',', array_map('intval', $followingIds));

            if ($idsCsv !== '') {
                $query->orderByRaw("case when user_id in ($idsCsv) then 1 else 0 end desc");
            }
        }

        return $query
            ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function getLiveSpacesProperty()
    {
        if (! Auth::check()) {
            return collect();
        }

        $viewer = Auth::user();
        $followingIds = $viewer->following()->pluck('users.id')->push($viewer->id);
        $exclude = $viewer->excludedUserIds();

        return Space::query()
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->whereIn('host_user_id', $followingIds)
            ->when($exclude->isNotEmpty(), fn ($q) => $q->whereNotIn('host_user_id', $exclude))
            ->with(['host'])
            ->latest('started_at')
            ->limit(8)
            ->get();
    }

    public function getRecommendedUsersProperty()
    {
        if (! Auth::check()) {
            return collect();
        }

        return app(DiscoverService::class)->recommendedUsers(Auth::user(), 5);
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

    public function getUpcomingSpacesProperty()
    {
        if (! Auth::check()) {
            return collect();
        }

        $viewer = Auth::user();
        $followingIds = $viewer->following()->pluck('users.id')->push($viewer->id);
        $exclude = $viewer->excludedUserIds();

        return Space::query()
            ->whereNull('started_at')
            ->whereNull('ended_at')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '>=', now())
            ->whereIn('host_user_id', $followingIds)
            ->when($exclude->isNotEmpty(), fn ($q) => $q->whereNotIn('host_user_id', $exclude))
            ->with(['host'])
            ->orderBy('scheduled_for')
            ->limit(8)
            ->get();
    }

    private function normalizedViewerLocation(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        $value = trim((string) (Auth::user()->location ?? ''));

        return $value === '' ? null : mb_substr($value, 0, 60);
    }

    public function getTrendingHashtagsProperty()
    {
        return app(TrendingService::class)->trendingHashtags(Auth::user(), 8, $this->normalizedViewerLocation());
    }

    public function getTrendingKeywordsProperty()
    {
        return app(TrendingService::class)->trendingKeywords(Auth::user(), 8, $this->normalizedViewerLocation());
    }

    public function checkForNewPosts(): void
    {
        if (! $this->latestSeenAt) {
            return;
        }

        $this->hasNewPosts = $this->baseQuery()
            ->where('created_at', '>', $this->latestSeenAt)
            ->exists();
    }

    public function refreshTimeline(): void
    {
        $this->resetPage();
        $value = $this->baseQuery()->max('created_at');
        $this->latestSeenAt = $value ? (string) $value : null;
        $this->hasNewPosts = false;
    }

    public function render()
    {
        return view('livewire.timeline-page')->layout('layouts.app');
    }
}
