<?php

namespace App\Http\Controllers;

use App\Http\Requests\Analytics\ExportAnalyticsRequest;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExportController
{
    public function __invoke(ExportAnalyticsRequest $request): StreamedResponse
    {
        $user = $request->user();

        $days = match ($request->validated('range')) {
            '7d' => 7,
            '28d' => 28,
            '90d' => 90,
            default => 28,
        };

        $now = now();
        $sinceDay = $now->copy()->subDays($days - 1)->toDateString();
        $untilDay = $now->toDateString();
        $sinceDateTime = $now->copy()->subDays($days);

        $postsQuery = Post::query()
            ->select(['id', 'created_at', 'body'])
            ->where('user_id', $user->id)
            ->where('body', '!=', '')
            ->where('created_at', '>=', $sinceDateTime)
            ->withCount([
                'likes as likes_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
                'reposts as reposts_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
                'replies as replies_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $counts = [];

        $analyticsByPost = DB::table('analytics_uniques')
            ->select('analytics_uniques.entity_id', 'analytics_uniques.type', DB::raw('count(*) as count'))
            ->join('posts', 'posts.id', '=', 'analytics_uniques.entity_id')
            ->where('posts.user_id', $user->id)
            ->where('posts.body', '!=', '')
            ->where('posts.created_at', '>=', $sinceDateTime)
            ->whereIn('analytics_uniques.type', ['post_view', 'post_link_click', 'post_profile_click', 'post_media_view'])
            ->whereBetween('analytics_uniques.day', [$sinceDay, $untilDay])
            ->groupBy('analytics_uniques.entity_id', 'analytics_uniques.type')
            ->get();

        foreach ($analyticsByPost as $row) {
            $counts[$row->type][$row->entity_id] = (int) $row->count;
        }

        $filename = 'tweets-analytics-'.$days.'d-'.$now->toDateString().'.csv';

        return response()->streamDownload(function () use ($postsQuery, $counts): void {
            $out = fopen('php://output', 'wb');
            if ($out === false) {
                return;
            }

            fputcsv($out, [
                'post_id',
                'created_at',
                'post_url',
                'body',
                'impressions',
                'engagements',
                'engagement_rate',
                'link_clicks',
                'profile_clicks',
                'media_views',
                'likes',
                'reposts',
                'replies',
            ]);

            foreach ($postsQuery->cursor() as $post) {
                $impressions = (int) ($counts['post_view'][$post->id] ?? 0);
                $linkClicks = (int) ($counts['post_link_click'][$post->id] ?? 0);
                $profileClicks = (int) ($counts['post_profile_click'][$post->id] ?? 0);
                $mediaViews = (int) ($counts['post_media_view'][$post->id] ?? 0);

                $likes = (int) ($post->likes_count_range ?? 0);
                $reposts = (int) ($post->reposts_count_range ?? 0);
                $replies = (int) ($post->replies_count_range ?? 0);

                $engagements = $likes + $reposts + $replies + $linkClicks + $profileClicks + $mediaViews;
                $engagementRate = $impressions > 0 ? ($engagements / $impressions) : 0.0;

                fputcsv($out, [
                    $post->id,
                    $post->created_at?->toIso8601String(),
                    route('posts.show', $post),
                    $post->body,
                    $impressions,
                    $engagements,
                    number_format($engagementRate * 100, 1, '.', ''),
                    $linkClicks,
                    $profileClicks,
                    $mediaViews,
                    $likes,
                    $reposts,
                    $replies,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
