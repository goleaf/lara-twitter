<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExportController
{
    public function __invoke(Request $request): StreamedResponse
    {
        abort_unless(Auth::check(), 403);

        $user = Auth::user();
        abort_unless($user->analytics_enabled || $user->is_admin, 403);

        $days = match ($request->query('range')) {
            '7d' => 7,
            '28d' => 28,
            '90d' => 90,
            default => 28,
        };

        $sinceDay = now()->subDays($days - 1)->toDateString();
        $untilDay = now()->toDateString();
        $sinceDateTime = now()->subDays($days);

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->where('body', '!=', '')
            ->where('created_at', '>=', $sinceDateTime)
            ->withCount([
                'likes as likes_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
                'reposts as reposts_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
                'replies as replies_count_range' => fn ($q) => $q->where('created_at', '>=', $sinceDateTime),
            ])
            ->latest()
            ->get();

        $postIds = $posts->pluck('id')->all();

        $counts = [];

        if (! empty($postIds)) {
            $analyticsByPost = DB::table('analytics_uniques')
                ->select('entity_id', 'type', DB::raw('count(*) as count'))
                ->whereIn('entity_id', $postIds)
                ->whereIn('type', ['post_view', 'post_link_click', 'post_profile_click', 'post_media_view'])
                ->whereBetween('day', [$sinceDay, $untilDay])
                ->groupBy('entity_id', 'type')
                ->get();

            foreach ($analyticsByPost as $row) {
                $counts[$row->type][$row->entity_id] = (int) $row->count;
            }
        }

        $filename = 'tweets-analytics-'.$days.'d-'.now()->toDateString().'.csv';

        return response()->streamDownload(function () use ($posts, $counts): void {
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

            foreach ($posts as $post) {
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

