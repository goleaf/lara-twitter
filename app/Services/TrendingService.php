<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TrendingService
{
    private function recentWindowStart(): \Illuminate\Support\Carbon
    {
        return now()->subHour();
    }

    public function trendingHashtags(?User $viewer, int $limit = 10, ?string $location = null): Collection
    {
        $since = now()->subDay();
        $recentSince = $this->recentWindowStart();

        $query = Hashtag::query()
            ->select(['hashtags.*'])
            ->selectRaw('count(*) as uses_count')
            ->selectRaw('count(distinct posts.user_id) as users_count')
            ->selectRaw('sum(case when posts.created_at >= ? then 1 else 0 end) as recent_uses_count', [$recentSince])
            ->selectRaw('count(distinct case when posts.created_at >= ? then posts.user_id end) as recent_users_count', [$recentSince])
            ->join('hashtag_post', 'hashtag_post.hashtag_id', '=', 'hashtags.id')
            ->join('posts', 'posts.id', '=', 'hashtag_post.post_id')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->whereNull('posts.reply_to_id')
            ->where('posts.is_reply_like', false)
            ->where('posts.created_at', '>=', $since)
            ->groupBy('hashtags.id');

        $location = is_string($location) ? trim($location) : null;
        if ($location) {
            $needle = '%'.mb_strtolower($location).'%';
            $query->whereRaw('lower(users.location) like ?', [$needle]);
        }

        $candidates = $query
            ->orderByDesc('recent_uses_count')
            ->orderByDesc('uses_count')
            ->limit(max(25, $limit * 8))
            ->get();

        $mutedTerms = $this->activeMutedTerms($viewer);
        if ($mutedTerms->isNotEmpty()) {
            $matcher = app(MutedTermMatcher::class);
            $candidates = $candidates->reject(function (Hashtag $tag) use ($mutedTerms, $matcher): bool {
                $text = '#'.(string) $tag->tag;

                foreach ($mutedTerms as $term) {
                    if ($matcher->matches($text, $term)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $candidates = $candidates->map(function (Hashtag $tag) {
            $recent = (int) ($tag->recent_uses_count ?? 0);
            $total = (int) ($tag->uses_count ?? 0);
            $baseline = max(0, $total - $recent);
            $recentUsers = (int) ($tag->recent_users_count ?? 0);

            $tag->trend_score = ($recent * $recent) / ($baseline + 1) + ($total * 0.01) + ($recentUsers * 0.05);

            return $tag;
        });

        $interests = $this->normalizedInterests($viewer);

        if (count($interests)) {
            [$preferred, $other] = $candidates->partition(fn (Hashtag $tag): bool => in_array(mb_strtolower((string) $tag->tag), $interests, true));
            $preferred = $preferred->sortByDesc(fn (Hashtag $tag) => (float) ($tag->trend_score ?? 0));
            $other = $other->sortByDesc(fn (Hashtag $tag) => (float) ($tag->trend_score ?? 0));

            return $preferred->merge($other)->values()->take($limit);
        }

        return $candidates
            ->sortByDesc(fn (Hashtag $tag) => (float) ($tag->trend_score ?? 0))
            ->values()
            ->take($limit);
    }

    public function trendingConversations(?User $viewer, int $limit = 10, ?string $location = null): Collection
    {
        $since = now()->subDay();

        $query = Post::query()
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts', 'replies'])
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->where('posts.created_at', '>=', $since);

        $location = is_string($location) ? trim($location) : null;
        if ($location) {
            $needle = '%'.mb_strtolower($location).'%';
            $query->whereHas('user', fn ($q) => $q->whereRaw('lower(location) like ?', [$needle]));
        }

        if ($viewer) {
            $excluded = $viewer->excludedUserIds();
            if ($excluded->isNotEmpty()) {
                $query->whereNotIn('posts.user_id', $excluded);
            }

            $this->applyMutedTermsToPostsQuery($query, $viewer);
        }

        return $query
            ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
            ->orderByDesc('posts.created_at')
            ->limit($limit)
            ->get();
    }

    public function trendingKeywords(?User $viewer, int $limit = 15, ?string $location = null): Collection
    {
        $since = now()->subDay();
        $recentSince = $this->recentWindowStart();

        $postsQuery = Post::query()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->where('posts.created_at', '>=', $since)
            ->latest('posts.created_at')
            ->limit(800)
            ->select(['posts.body', 'posts.created_at', 'posts.user_id']);

        $location = is_string($location) ? trim($location) : null;
        if ($location) {
            $needle = '%'.mb_strtolower($location).'%';
            $postsQuery
                ->join('users', 'users.id', '=', 'posts.user_id')
                ->whereRaw('lower(users.location) like ?', [$needle])
                ->select(['posts.body', 'posts.created_at', 'posts.user_id']);
        }

        if ($viewer) {
            $excluded = $viewer->excludedUserIds();
            if ($excluded->isNotEmpty()) {
                $postsQuery->whereNotIn('posts.user_id', $excluded);
            }
        }

        $posts = $postsQuery->get();

        $stopwords = array_fill_keys([
            'the', 'and', 'for', 'with', 'this', 'that', 'from', 'your', 'you', 'are', 'was', 'were', 'have', 'has',
            'not', 'but', 'all', 'can', 'will', 'just', 'like', 'about', 'into', 'over', 'when', 'what', 'why', 'how',
            'than', 'then', 'them', 'they', 'our', 'out', 'who', 'its', 'it', 'a', 'an', 'to', 'of', 'in', 'on', 'at',
            'is', 'as', 'be', 'or', 'we', 'i', 'me', 'my',
        ], true);

        $counts = [];
        $recentCounts = [];
        $userCounts = [];
        $recentUserCounts = [];

        foreach ($posts as $post) {
            $body = preg_replace('/https?:\\/\\/\\S+/i', ' ', (string) $post->body) ?? (string) $post->body;
            $body = preg_replace('/[#@][A-Za-z0-9_\\-]+/u', ' ', $body) ?? $body;
            $isRecent = (bool) ($post->created_at && $post->created_at->greaterThanOrEqualTo($recentSince));

            if (! preg_match_all('/\\b[\\pL\\pN]{4,}\\b/u', $body, $matches)) {
                continue;
            }

            foreach ($matches[0] as $word) {
                $w = mb_strtolower($word);

                if (isset($stopwords[$w])) {
                    continue;
                }

                $counts[$w] = ($counts[$w] ?? 0) + 1;
                $userCounts[$w][$post->user_id] = true;

                if ($isRecent) {
                    $recentCounts[$w] = ($recentCounts[$w] ?? 0) + 1;
                    $recentUserCounts[$w][$post->user_id] = true;
                }
            }
        }

        $mutedTerms = $this->activeMutedTerms($viewer);

        $rows = collect($counts)->map(function (int $count, string $word) use ($recentCounts, $userCounts, $recentUserCounts) {
            $recent = (int) ($recentCounts[$word] ?? 0);
            $baseline = max(0, $count - $recent);
            $users = isset($userCounts[$word]) ? count($userCounts[$word]) : 0;
            $recentUsers = isset($recentUserCounts[$word]) ? count($recentUserCounts[$word]) : 0;

            $score = ($recent * $recent) / ($baseline + 1) + ($count * 0.01) + ($recentUsers * 0.05) + ($users * 0.005);

            return [
                'keyword' => $word,
                'count' => $count,
                'recent_count' => $recent,
                'users_count' => $users,
                'recent_users_count' => $recentUsers,
                'score' => $score,
            ];
        });

        if ($mutedTerms->isNotEmpty()) {
            $matcher = app(MutedTermMatcher::class);
            $rows = $rows->reject(function (array $row) use ($mutedTerms, $matcher): bool {
                foreach ($mutedTerms as $term) {
                    if ($matcher->matches($row['keyword'], $term)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $rows = $rows
            ->sortByDesc(fn (array $row) => (float) ($row['score'] ?? 0))
            ->values()
            ->take($limit)
            ->map(function (array $row) {
                unset($row['score']);

                return $row;
            });

        return $rows;
    }

    private function normalizedInterests(?User $viewer): array
    {
        $raw = $viewer?->interest_hashtags ?? [];

        return collect($raw)
            ->filter(fn ($t) => is_string($t) && $t !== '')
            ->map(fn ($t) => mb_strtolower(ltrim($t, '#')))
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

            $matchSql = null;
            $matchArg = null;

            if ($term->whole_word && preg_match('/^[a-z0-9_]+$/i', $needle)) {
                $matchSql = "(' ' || lower(posts.body) || ' ') like ?";
                $matchArg = '% '.$needle.' %';
            } else {
                $matchSql = 'lower(posts.body) like ?';
                $matchArg = '%'.$needle.'%';
            }

            if ($term->only_non_followed && count($followingIds)) {
                $query->where(function ($q) use ($followingIds, $matchSql, $matchArg): void {
                    $q->whereIn('posts.user_id', $followingIds)->orWhereRaw($matchSql.' = 0', [$matchArg]);
                });
            } else {
                $query->whereRaw($matchSql.' = 0', [$matchArg]);
            }
        }
    }

    private function activeMutedTerms(?User $viewer): Collection
    {
        if (! $viewer) {
            return collect();
        }

        return $viewer->mutedTerms()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where('mute_timeline', true)
            ->latest()
            ->limit(50)
            ->get();
    }
}
