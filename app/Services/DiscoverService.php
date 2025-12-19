<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiscoverService
{
    public function categoryHashtags(): array
    {
        return [
            'news' => ['news', 'world', 'tech', 'business'],
            'sports' => ['sports', 'football', 'soccer', 'nba', 'f1'],
            'entertainment' => ['music', 'movies', 'tv', 'gaming'],
            'technology' => ['tech', 'ai', 'programming', 'laravel', 'php'],
        ];
    }

    public function recommendedUsers(?User $viewer, int $limit = 10): Collection
    {
        if (! $viewer) {
            return User::query()
                ->withCount('followers')
                ->orderByDesc('followers_count')
                ->latest()
                ->limit($limit)
                ->get();
        }

        $followingIds = $viewer->following()->pluck('users.id')->all();
        $blockedIds = $viewer->blocksInitiated()->pluck('blocked_id')->all();
        $blockedByIds = $viewer->blocksReceived()->pluck('blocker_id')->all();
        $excludeIds = array_values(array_unique(array_merge([$viewer->id], $followingIds)));
        $excludeIds = array_values(array_unique(array_merge($excludeIds, $blockedIds, $blockedByIds)));

        $mutuals = DB::table('follows')
            ->select('followed_id', DB::raw('count(*) as mutual_count'))
            ->whereIn('follower_id', $followingIds ?: [-1])
            ->whereNotIn('followed_id', $excludeIds)
            ->groupBy('followed_id')
            ->orderByDesc('mutual_count')
            ->limit($limit * 3)
            ->get()
            ->keyBy('followed_id');

        $candidateIds = $mutuals->keys()->all();

        $users = collect();

        if (! empty($candidateIds)) {
            $users = User::query()
                ->withCount('followers')
                ->whereIn('id', $candidateIds)
                ->get()
                ->each(function (User $u) use ($mutuals) {
                    $u->setAttribute('mutual_count', (int) ($mutuals[$u->id]->mutual_count ?? 0));
                })
                ->sortByDesc(fn (User $u) => (int) ($u->getAttribute('mutual_count') ?? 0))
                ->values()
                ->take($limit);
        }

        $remaining = $limit - $users->count();
        if ($remaining <= 0) {
            return $users;
        }

        $fallback = User::query()
            ->withCount('followers')
            ->whereNotIn('id', array_merge($excludeIds, $users->pluck('id')->all()))
            ->orderByDesc('followers_count')
            ->latest()
            ->limit($remaining)
            ->get();

        return $users->concat($fallback)->values();
    }

    public function forYouPosts(?User $viewer, int $limit = 15)
    {
        $query = Post::query()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->withCount(['likes', 'reposts', 'replies']);

        $this->applyViewerExclusions($query, $viewer);

        if ($viewer) {
            $query->where('user_id', '!=', $viewer->id);

            $followingIds = $viewer->following()->pluck('users.id')->all();
            $idsCsv = implode(',', array_map('intval', $followingIds));
            if ($idsCsv !== '') {
                $query->orderByRaw("case when user_id in ($idsCsv) then 1 else 0 end asc");
            }
        }

        return $query
            ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function categoryPosts(string $category, ?User $viewer, int $limit = 15)
    {
        $map = $this->categoryHashtags();
        $tags = $map[$category] ?? [];

        $query = Post::query()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->withCount(['likes', 'reposts', 'replies']);

        $this->applyViewerExclusions($query, $viewer);

        if (count($tags)) {
            $query->whereHas('hashtags', fn ($q) => $q->whereIn('tag', $tags));
        }

        if ($viewer) {
            $query->where('user_id', '!=', $viewer->id);

            $followingIds = $viewer->following()->pluck('users.id')->all();
            $idsCsv = implode(',', array_map('intval', $followingIds));
            if ($idsCsv !== '') {
                $query->orderByRaw("case when user_id in ($idsCsv) then 1 else 0 end asc");
            }
        }

        return $query
            ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function topPostsForHashtags(array $tags, ?User $viewer, int $perTag = 2, int $limitTags = 5): Collection
    {
        $normalized = collect($tags)
            ->filter(fn ($t) => is_string($t) && trim($t) !== '')
            ->map(fn ($t) => mb_strtolower(ltrim(trim($t), '#')))
            ->unique()
            ->take($limitTags)
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        $hashtagIds = Hashtag::query()
            ->whereIn('tag', $normalized->all())
            ->pluck('id', 'tag');

        if ($hashtagIds->isEmpty()) {
            return collect();
        }

        $query = Post::query()
            ->select('posts.*', 'hashtag_post.hashtag_id')
            ->join('hashtag_post', 'hashtag_post.post_id', '=', 'posts.id')
            ->whereIn('hashtag_post.hashtag_id', $hashtagIds->values()->all())
            ->whereNull('posts.reply_to_id')
            ->where('posts.is_reply_like', false)
            ->where('posts.created_at', '>=', now()->subDay())
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->withCount(['likes', 'reposts', 'replies']);

        $this->applyViewerExclusions($query, $viewer);

        if ($viewer) {
            $query->where('posts.user_id', '!=', $viewer->id);
        }

        $posts = $query
            ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
            ->orderByDesc('posts.created_at')
            ->limit(250)
            ->get();

        $byHashtag = $posts->groupBy('hashtag_id');

        return $normalized
            ->mapWithKeys(function (string $tag) use ($hashtagIds, $byHashtag, $perTag) {
                $id = (int) ($hashtagIds[$tag] ?? 0);

                $items = $id ? ($byHashtag->get($id, collect())->take($perTag)->values()) : collect();

                return [$tag => $items];
            });
    }

    private function applyViewerExclusions(Builder $query, ?User $viewer): void
    {
        if (! $viewer) {
            return;
        }

        $mutedIds = $viewer->mutesInitiated()->pluck('muted_id');
        $blockedIds = $viewer->blocksInitiated()->pluck('blocked_id');
        $blockedByIds = $viewer->blocksReceived()->pluck('blocker_id');

        $exclude = $mutedIds->merge($blockedIds)->merge($blockedByIds)->unique()->values();
        if ($exclude->isNotEmpty()) {
            $query->whereNotIn('user_id', $exclude);
        }
    }
}
