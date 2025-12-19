<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;

class TrendingService
{
    public function trendingHashtags(?User $viewer, int $limit = 10): Collection
    {
        $since = now()->subDay();

        $query = Hashtag::query()
            ->select(['hashtags.*'])
            ->selectRaw('count(*) as uses_count')
            ->join('hashtag_post', 'hashtag_post.hashtag_id', '=', 'hashtags.id')
            ->join('posts', 'posts.id', '=', 'hashtag_post.post_id')
            ->whereNull('posts.reply_to_id')
            ->where('posts.is_reply_like', false)
            ->where('posts.created_at', '>=', $since)
            ->groupBy('hashtags.id');

        $interests = $this->normalizedInterests($viewer);
        if (count($interests)) {
            $placeholders = implode(',', array_fill(0, count($interests), '?'));
            $query->orderByRaw("case when hashtags.tag in ($placeholders) then 1 else 0 end desc", $interests);
        }

        return $query
            ->orderByDesc('uses_count')
            ->limit($limit)
            ->get();
    }

    public function trendingKeywords(int $limit = 15): Collection
    {
        $since = now()->subDay();

        $posts = Post::query()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(500)
            ->pluck('body')
            ->all();

        $stopwords = array_fill_keys([
            'the', 'and', 'for', 'with', 'this', 'that', 'from', 'your', 'you', 'are', 'was', 'were', 'have', 'has',
            'not', 'but', 'all', 'can', 'will', 'just', 'like', 'about', 'into', 'over', 'when', 'what', 'why', 'how',
            'than', 'then', 'them', 'they', 'our', 'out', 'who', 'its', 'it', 'a', 'an', 'to', 'of', 'in', 'on', 'at',
            'is', 'as', 'be', 'or', 'we', 'i', 'me', 'my',
        ], true);

        $counts = [];

        foreach ($posts as $body) {
            $body = preg_replace('/https?:\\/\\/\\S+/i', ' ', (string) $body) ?? (string) $body;
            $body = preg_replace('/[#@][A-Za-z0-9_\\-]+/u', ' ', $body) ?? $body;

            if (! preg_match_all('/\\b[\\pL\\pN]{4,}\\b/u', $body, $matches)) {
                continue;
            }

            foreach ($matches[0] as $word) {
                $w = mb_strtolower($word);

                if (isset($stopwords[$w])) {
                    continue;
                }

                $counts[$w] = ($counts[$w] ?? 0) + 1;
            }
        }

        arsort($counts);

        return collect(array_slice($counts, 0, $limit, true))
            ->map(fn ($count, $word) => ['keyword' => $word, 'count' => $count])
            ->values();
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
}
