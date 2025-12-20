<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use App\Support\SqlHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DiscoverService
{
    private function cacheTtl(): \DateTimeInterface
    {
        return now()->addSeconds(90);
    }

    private function cacheKey(string $type, ?User $viewer, array $parts = []): string
    {
        $viewerId = $viewer?->id ?? 0;
        $payload = $parts ? json_encode($parts) : '';

        return 'discover:'.$type.':'.$viewerId.':'.sha1((string) $payload);
    }

    public function categoryHashtags(): array
    {
        return [
            'news' => ['news', 'world', 'tech', 'business'],
            'politics' => ['politics', 'election', 'government', 'policy'],
            'sports' => ['sports', 'football', 'soccer', 'nba', 'f1'],
            'entertainment' => ['music', 'movies', 'tv', 'gaming'],
            'technology' => ['tech', 'ai', 'programming', 'laravel', 'php'],
        ];
    }

    public function recommendedUsers(?User $viewer, int $limit = 10): Collection
    {
        $key = $this->cacheKey('recommended-users', $viewer, [$limit]);

        return Cache::remember($key, $this->cacheTtl(), function () use ($viewer, $limit) {
            if (! $viewer) {
                return User::query()
                    ->withCount('followers')
                    ->orderByDesc('followers_count')
                    ->latest()
                    ->limit($limit)
                    ->get();
            }

            $followingIds = $viewer->followingIds()->all();
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

            $interestTags = $this->normalizedInterestHashtags($viewer);
            if (count($interestTags)) {
                $exclude = array_merge($excludeIds, $users->pluck('id')->all());

                $interestRows = DB::table('hashtag_post')
                    ->join('hashtags', 'hashtags.id', '=', 'hashtag_post.hashtag_id')
                    ->join('posts', 'posts.id', '=', 'hashtag_post.post_id')
                    ->select('posts.user_id', DB::raw('count(*) as interest_posts_count'))
                    ->whereIn('hashtags.tag', $interestTags)
                    ->where('posts.is_published', true)
                    ->whereNull('posts.reply_to_id')
                    ->where('posts.is_reply_like', false)
                    ->where('posts.created_at', '>=', now()->subDays(30))
                    ->when(count($exclude), fn ($q) => $q->whereNotIn('posts.user_id', $exclude))
                    ->groupBy('posts.user_id')
                    ->orderByDesc('interest_posts_count')
                    ->limit(max(10, $remaining * 5))
                    ->get()
                    ->keyBy('user_id');

                $interestCandidateIds = $interestRows->keys()->all();

                if (! empty($interestCandidateIds)) {
                    $interestUsers = User::query()
                        ->withCount('followers')
                        ->whereIn('id', $interestCandidateIds)
                        ->get()
                        ->each(function (User $u) use ($interestRows) {
                            $u->setAttribute('interest_posts_count', (int) ($interestRows[$u->id]->interest_posts_count ?? 0));
                        })
                        ->sort(function (User $a, User $b) {
                            $aCount = (int) ($a->getAttribute('interest_posts_count') ?? 0);
                            $bCount = (int) ($b->getAttribute('interest_posts_count') ?? 0);

                            if ($aCount !== $bCount) {
                                return $bCount <=> $aCount;
                            }

                            return ((int) ($b->followers_count ?? 0)) <=> ((int) ($a->followers_count ?? 0));
                        })
                        ->values()
                        ->take($remaining);

                    $users = $users->concat($interestUsers)->values();
                }
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
        });
    }

    public function forYouPosts(?User $viewer, int $limit = 15)
    {
        $key = $this->cacheKey('for-you-posts', $viewer, [$limit]);

        return Cache::remember($key, $this->cacheTtl(), function () use ($viewer, $limit) {
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
                $this->applyMutedTermsToPostsQuery($query, $viewer);

                $interestTags = $this->normalizedInterestHashtags($viewer);
                if (count($interestTags)) {
                    $placeholders = implode(',', array_fill(0, count($interestTags), '?'));
                    $query->orderByRaw(
                        "case when exists (select 1 from hashtag_post hp join hashtags h on h.id = hp.hashtag_id where hp.post_id = posts.id and h.tag in ($placeholders)) then 0 else 1 end asc",
                        $interestTags,
                    );
                }

                $query->where('user_id', '!=', $viewer->id);

                $followingIds = $viewer->followingIds()->all();
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
        });
    }

    public function categoryPosts(string $category, ?User $viewer, int $limit = 15)
    {
        $key = $this->cacheKey('category-posts', $viewer, [$category, $limit]);

        return Cache::remember($key, $this->cacheTtl(), function () use ($category, $viewer, $limit) {
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

            if ($viewer) {
                $this->applyMutedTermsToPostsQuery($query, $viewer);
            }

            if (count($tags)) {
                $query->whereHas('hashtags', fn ($q) => $q->whereIn('tag', $tags));
            }

            if ($viewer) {
                $query->where('user_id', '!=', $viewer->id);

                $followingIds = $viewer->followingIds()->all();
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
        });
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

        $key = $this->cacheKey('top-hashtag-posts', $viewer, [$normalized->all(), $perTag, $limitTags]);

        return Cache::remember($key, $this->cacheTtl(), function () use ($normalized, $viewer, $perTag) {
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
                $this->applyMutedTermsToPostsQuery($query, $viewer);
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
        });
    }

    private function applyViewerExclusions(Builder $query, ?User $viewer): void
    {
        if (! $viewer) {
            return;
        }

        $exclude = $viewer->excludedUserIds();
        if ($exclude->isNotEmpty()) {
            $query->whereNotIn('user_id', $exclude);
        }
    }

    private function normalizedInterestHashtags(?User $viewer): array
    {
        $raw = $viewer?->interest_hashtags ?? [];

        return collect($raw)
            ->filter(fn ($t) => is_string($t) && trim($t) !== '')
            ->map(fn ($t) => mb_strtolower(ltrim(trim($t), '#')))
            ->unique()
            ->take(20)
            ->values()
            ->all();
    }

    private function applyMutedTermsToPostsQuery(Builder $query, User $viewer): void
    {
        $terms = $this->activeMutedTerms($viewer);

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
                    $q->whereIn('posts.user_id', $followingIds)->orWhereRaw('('.$combinedMatchSql.') = 0', [$matchArg, $matchArg]);
                });
            } else {
                $query->whereRaw('('.$combinedMatchSql.') = 0', [$matchArg, $matchArg]);
            }
        }
    }

    private function activeMutedTerms(User $viewer): Collection
    {
        return $viewer->activeMutedTerms();
    }
}
