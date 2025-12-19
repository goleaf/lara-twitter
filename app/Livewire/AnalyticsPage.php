<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class AnalyticsPage extends Component
{
    #[Url]
    public string $tab = 'overview';

    #[Url]
    public string $range = '28d';

    #[Url]
    public string $sort = 'impressions';

    #[Url]
    public string $dir = 'desc';

    /** @var array<int, int>|null */
    private ?array $cachedPostIds = null;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);

        $user = Auth::user();

        abort_unless($user->analytics_enabled || $user->is_admin, 403);

        $this->tab = in_array($this->tab, ['overview', 'tweets', 'audience'], true) ? $this->tab : 'overview';
        $this->range = array_key_exists($this->range, $this->rangeDaysMap()) ? $this->range : '28d';
        $this->sort = in_array($this->sort, $this->sortOptions(), true) ? $this->sort : 'impressions';
        $this->dir = in_array($this->dir, ['asc', 'desc'], true) ? $this->dir : 'desc';
    }

    public function getSummaryProperty(): array
    {
        $userId = Auth::id();
        $days = $this->rangeDays();
        $sinceDay = now()->subDays($days)->toDateString();
        $sinceDateTime = now()->subDays($days);

        $postIds = $this->userPostIds();

        $impressions = empty($postIds) ? 0 : (int) DB::table('analytics_uniques')
            ->where('type', 'post_view')
            ->whereIn('entity_id', $postIds)
            ->where('day', '>=', $sinceDay)
            ->count();

        $profileVisits = (int) DB::table('analytics_uniques')
            ->where('type', 'profile_view')
            ->where('entity_id', $userId)
            ->where('day', '>=', $sinceDay)
            ->count();

        $mentions = (int) DB::table('mentions')
            ->join('posts', 'mentions.post_id', '=', 'posts.id')
            ->where('mentions.mentioned_user_id', $userId)
            ->where('posts.created_at', '>=', $sinceDateTime)
            ->count();

        $newFollowers = (int) DB::table('follows')
            ->where('followed_id', $userId)
            ->where('created_at', '>=', $sinceDateTime)
            ->count();

        $postsPublished = (int) DB::table('posts')
            ->where('user_id', $userId)
            ->where('body', '!=', '')
            ->where('created_at', '>=', $sinceDateTime)
            ->count();

        $likes = empty($postIds) ? 0 : (int) DB::table('likes')
            ->whereIn('post_id', $postIds)
            ->where('created_at', '>=', $sinceDateTime)
            ->count();

        $reposts = empty($postIds) ? 0 : (int) DB::table('posts')
            ->whereIn('repost_of_id', $postIds)
            ->where('created_at', '>=', $sinceDateTime)
            ->count();

        $replies = empty($postIds) ? 0 : (int) DB::table('posts')
            ->whereIn('reply_to_id', $postIds)
            ->where('created_at', '>=', $sinceDateTime)
            ->count();

        $linkClicks = empty($postIds) ? 0 : (int) DB::table('analytics_uniques')
            ->where('type', 'post_link_click')
            ->whereIn('entity_id', $postIds)
            ->where('day', '>=', $sinceDay)
            ->count();

        $profileClicks = empty($postIds) ? 0 : (int) DB::table('analytics_uniques')
            ->where('type', 'post_profile_click')
            ->whereIn('entity_id', $postIds)
            ->where('day', '>=', $sinceDay)
            ->count();

        $mediaViews = empty($postIds) ? 0 : (int) DB::table('analytics_uniques')
            ->where('type', 'post_media_view')
            ->whereIn('entity_id', $postIds)
            ->where('day', '>=', $sinceDay)
            ->count();

        $engagements = $likes + $reposts + $replies + $linkClicks + $profileClicks + $mediaViews;
        $engagementRate = $impressions > 0 ? $engagements / $impressions : 0.0;

        return [
            'days' => $days,
            'impressions' => $impressions,
            'profile_visits' => $profileVisits,
            'mentions' => $mentions,
            'new_followers' => $newFollowers,
            'posts_published' => $postsPublished,
            'likes' => $likes,
            'reposts' => $reposts,
            'replies' => $replies,
            'link_clicks' => $linkClicks,
            'profile_clicks' => $profileClicks,
            'media_views' => $mediaViews,
            'engagements' => $engagements,
            'engagement_rate' => $engagementRate,
        ];
    }

    public function getTopPostsProperty(): Collection
    {
        $days = $this->rangeDays();
        $sinceDay = now()->subDays($days)->toDateString();
        $sinceDateTime = now()->subDays($days);

        $postIds = $this->userPostIds();
        if (empty($postIds)) {
            return collect();
        }

        $impressionsByPost = DB::table('analytics_uniques')
            ->select('entity_id', DB::raw('count(*) as impressions'))
            ->where('type', 'post_view')
            ->whereIn('entity_id', $postIds)
            ->where('day', '>=', $sinceDay)
            ->groupBy('entity_id')
            ->orderByDesc('impressions')
            ->limit(10)
            ->get()
            ->keyBy('entity_id');

        if ($impressionsByPost->isEmpty()) {
            return collect();
        }

        $topPostIds = $impressionsByPost->keys()->all();

        $analyticsByPost = DB::table('analytics_uniques')
            ->select('entity_id', 'type', DB::raw('count(*) as count'))
            ->whereIn('entity_id', $topPostIds)
            ->whereIn('type', ['post_link_click', 'post_profile_click', 'post_media_view'])
            ->where('day', '>=', $sinceDay)
            ->groupBy('entity_id', 'type')
            ->get();

        $extraCounts = [];
        foreach ($analyticsByPost as $row) {
            $extraCounts[$row->type][$row->entity_id] = (int) $row->count;
        }

        $posts = Post::query()
            ->whereIn('id', $topPostIds)
            ->withCount([
                'likes as likes_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
                'reposts as reposts_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
                'replies as replies_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
            ])
            ->latest()
            ->get()
            ->sortByDesc(fn (Post $p) => (int) ($impressionsByPost[$p->id]->impressions ?? 0))
            ->values();

        return $posts->map(function (Post $post) use ($impressionsByPost, $extraCounts) {
            $impressions = (int) ($impressionsByPost[$post->id]->impressions ?? 0);
            $linkClicks = (int) ($extraCounts['post_link_click'][$post->id] ?? 0);
            $profileClicks = (int) ($extraCounts['post_profile_click'][$post->id] ?? 0);
            $mediaViews = (int) ($extraCounts['post_media_view'][$post->id] ?? 0);

            $likes = (int) ($post->likes_count_range ?? 0);
            $reposts = (int) ($post->reposts_count_range ?? 0);
            $replies = (int) ($post->replies_count_range ?? 0);

            $engagements = $likes + $reposts + $replies + $linkClicks + $profileClicks + $mediaViews;
            $engagementRate = $impressions > 0 ? $engagements / $impressions : 0.0;

            $post->analytics_impressions = $impressions;
            $post->analytics_link_clicks = $linkClicks;
            $post->analytics_profile_clicks = $profileClicks;
            $post->analytics_media_views = $mediaViews;
            $post->analytics_engagements = $engagements;
            $post->analytics_engagement_rate = $engagementRate;

            return $post;
        });
    }

    public function getTweetRowsProperty(): Collection
    {
        $userId = Auth::id();
        $days = $this->rangeDays();
        $sinceDay = now()->subDays($days)->toDateString();
        $sinceDateTime = now()->subDays($days);

        $posts = Post::query()
            ->where('user_id', $userId)
            ->where('body', '!=', '')
            ->where('created_at', '>=', $sinceDateTime)
            ->withCount([
                'likes as likes_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
                'reposts as reposts_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
                'replies as replies_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
            ])
            ->latest()
            ->limit(100)
            ->get();

        $postIds = $posts->pluck('id')->all();
        if (empty($postIds)) {
            return collect();
        }

        $analyticsByPost = DB::table('analytics_uniques')
            ->select('entity_id', 'type', DB::raw('count(*) as count'))
            ->whereIn('entity_id', $postIds)
            ->whereIn('type', ['post_view', 'post_link_click', 'post_profile_click', 'post_media_view'])
            ->where('day', '>=', $sinceDay)
            ->groupBy('entity_id', 'type')
            ->get();

        $counts = [];
        foreach ($analyticsByPost as $row) {
            $counts[$row->type][$row->entity_id] = (int) $row->count;
        }

        $rows = $posts->map(function (Post $post) use ($counts) {
            $impressions = (int) ($counts['post_view'][$post->id] ?? 0);
            $linkClicks = (int) ($counts['post_link_click'][$post->id] ?? 0);
            $profileClicks = (int) ($counts['post_profile_click'][$post->id] ?? 0);
            $mediaViews = (int) ($counts['post_media_view'][$post->id] ?? 0);

            $likes = (int) ($post->likes_count_range ?? 0);
            $reposts = (int) ($post->reposts_count_range ?? 0);
            $replies = (int) ($post->replies_count_range ?? 0);

            $engagements = $likes + $reposts + $replies + $linkClicks + $profileClicks + $mediaViews;
            $engagementRate = $impressions > 0 ? $engagements / $impressions : 0.0;

            $post->analytics_impressions = $impressions;
            $post->analytics_link_clicks = $linkClicks;
            $post->analytics_profile_clicks = $profileClicks;
            $post->analytics_media_views = $mediaViews;
            $post->analytics_engagements = $engagements;
            $post->analytics_engagement_rate = $engagementRate;

            return $post;
        });

        return $this->sortTweetRows($rows);
    }

    public function getFollowerGrowthProperty(): Collection
    {
        $userId = Auth::id();
        $days = $this->rangeDays();
        $sinceDateTime = now()->subDays($days);

        return DB::table('follows')
            ->select(DB::raw('date(created_at) as day'), DB::raw('count(*) as followers'))
            ->where('followed_id', $userId)
            ->where('created_at', '>=', $sinceDateTime)
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }

    public function getTopFollowerLocationsProperty(): Collection
    {
        $userId = Auth::id();

        return DB::table('follows')
            ->join('users', 'users.id', '=', 'follows.follower_id')
            ->where('follows.followed_id', $userId)
            ->whereNotNull('users.location')
            ->where('users.location', '!=', '')
            ->select('users.location', DB::raw('count(*) as followers'))
            ->groupBy('users.location')
            ->orderByDesc('followers')
            ->limit(10)
            ->get();
    }

    public function getAlsoFollowedAccountsProperty(): Collection
    {
        $userId = Auth::id();

        return DB::table('follows as f1')
            ->join('follows as f2', 'f2.follower_id', '=', 'f1.follower_id')
            ->join('users', 'users.id', '=', 'f2.followed_id')
            ->where('f1.followed_id', $userId)
            ->where('f2.followed_id', '!=', $userId)
            ->select('users.username', 'users.name', DB::raw('count(*) as followers'))
            ->groupBy('users.username', 'users.name')
            ->orderByDesc('followers')
            ->limit(10)
            ->get();
    }

    private function rangeDays(): int
    {
        return (int) ($this->rangeDaysMap()[$this->range] ?? 28);
    }

    /**
     * @return array<string, int>
     */
    private function rangeDaysMap(): array
    {
        return [
            '7d' => 7,
            '28d' => 28,
            '90d' => 90,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function sortOptions(): array
    {
        return [
            'date',
            'impressions',
            'engagements',
            'engagement_rate',
            'link_clicks',
            'profile_clicks',
            'media_views',
            'likes',
            'reposts',
            'replies',
        ];
    }

    /**
     * @return array<int, int>
     */
    private function userPostIds(): array
    {
        if (is_array($this->cachedPostIds)) {
            return $this->cachedPostIds;
        }

        $this->cachedPostIds = Post::query()
            ->where('user_id', Auth::id())
            ->pluck('id')
            ->all();

        return $this->cachedPostIds;
    }

    private function sortTweetRows(Collection $rows): Collection
    {
        $sort = $this->sort;
        $dir = $this->dir;

        $valueFn = match ($sort) {
            'date' => static fn (Post $p) => $p->created_at?->timestamp ?? 0,
            'impressions' => static fn (Post $p) => (int) ($p->analytics_impressions ?? 0),
            'engagements' => static fn (Post $p) => (int) ($p->analytics_engagements ?? 0),
            'engagement_rate' => static fn (Post $p) => (float) ($p->analytics_engagement_rate ?? 0),
            'link_clicks' => static fn (Post $p) => (int) ($p->analytics_link_clicks ?? 0),
            'profile_clicks' => static fn (Post $p) => (int) ($p->analytics_profile_clicks ?? 0),
            'media_views' => static fn (Post $p) => (int) ($p->analytics_media_views ?? 0),
            'likes' => static fn (Post $p) => (int) ($p->likes_count_range ?? 0),
            'reposts' => static fn (Post $p) => (int) ($p->reposts_count_range ?? 0),
            'replies' => static fn (Post $p) => (int) ($p->replies_count_range ?? 0),
            default => static fn (Post $p) => (int) ($p->analytics_impressions ?? 0),
        };

        $sorted = $dir === 'asc'
            ? $rows->sortBy($valueFn)
            : $rows->sortByDesc($valueFn);

        return $sorted->values();
    }

    public function render()
    {
        return view('livewire.analytics-page')->layout('layouts.app');
    }
}
