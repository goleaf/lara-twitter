<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
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

        if (empty($candidateIds)) {
            return collect();
        }

        $users = User::query()
            ->whereIn('id', $candidateIds)
            ->get()
            ->sortByDesc(fn (User $u) => (int) ($mutuals[$u->id]->mutual_count ?? 0))
            ->values()
            ->take($limit);

        return $users;
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

        if (count($tags)) {
            $query->whereHas('hashtags', fn ($q) => $q->whereIn('tag', $tags));
        }

        if ($viewer) {
            $followingIds = $viewer->following()->pluck('users.id')->push($viewer->id)->all();
            $idsCsv = implode(',', array_map('intval', $followingIds));
            if ($idsCsv !== '') {
                $query->orderByRaw("case when user_id in ($idsCsv) then 1 else 0 end desc");
            }
        }

        return $query
            ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
