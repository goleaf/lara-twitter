<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AnalyticsPage extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::check(), 403);

        $user = Auth::user();

        abort_unless($user->analytics_enabled || $user->is_admin, 403);
    }

    public function getSummaryProperty(): array
    {
        $userId = Auth::id();
        $since7 = now()->subDays(7)->toDateString();
        $since30 = now()->subDays(30)->toDateString();

        $postIds = Post::query()->where('user_id', $userId)->pluck('id')->all();

        $postViews7 = empty($postIds) ? 0 : (int) DB::table('analytics_uniques')
            ->where('type', 'post_view')
            ->whereIn('entity_id', $postIds)
            ->where('day', '>=', $since7)
            ->count();

        $postViews30 = empty($postIds) ? 0 : (int) DB::table('analytics_uniques')
            ->where('type', 'post_view')
            ->whereIn('entity_id', $postIds)
            ->where('day', '>=', $since30)
            ->count();

        $profileViews7 = (int) DB::table('analytics_uniques')
            ->where('type', 'profile_view')
            ->where('entity_id', $userId)
            ->where('day', '>=', $since7)
            ->count();

        $profileViews30 = (int) DB::table('analytics_uniques')
            ->where('type', 'profile_view')
            ->where('entity_id', $userId)
            ->where('day', '>=', $since30)
            ->count();

        $followers7 = (int) DB::table('follows')
            ->where('followed_id', $userId)
            ->whereDate('created_at', '>=', $since7)
            ->count();

        $followers30 = (int) DB::table('follows')
            ->where('followed_id', $userId)
            ->whereDate('created_at', '>=', $since30)
            ->count();

        return [
            'post_views_7d' => $postViews7,
            'post_views_30d' => $postViews30,
            'profile_views_7d' => $profileViews7,
            'profile_views_30d' => $profileViews30,
            'new_followers_7d' => $followers7,
            'new_followers_30d' => $followers30,
        ];
    }

    public function getTopPostsProperty()
    {
        $userId = Auth::id();
        $since7 = now()->subDays(7)->toDateString();

        $postIds = Post::query()->where('user_id', $userId)->pluck('id')->all();
        if (empty($postIds)) {
            return collect();
        }

        $viewsByPost = DB::table('analytics_uniques')
            ->select('entity_id', DB::raw('count(*) as views'))
            ->where('type', 'post_view')
            ->whereIn('entity_id', $postIds)
            ->where('day', '>=', $since7)
            ->groupBy('entity_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->keyBy('entity_id');

        $posts = Post::query()
            ->whereIn('id', $viewsByPost->keys()->all())
            ->withCount(['likes', 'reposts', 'replies'])
            ->latest()
            ->get()
            ->sortByDesc(fn (Post $p) => (int) ($viewsByPost[$p->id]->views ?? 0))
            ->values();

        return $posts->map(function (Post $post) use ($viewsByPost) {
            $post->analytics_views_7d = (int) ($viewsByPost[$post->id]->views ?? 0);
            return $post;
        });
    }

    public function render()
    {
        return view('livewire.analytics-page')->layout('layouts.app');
    }
}

