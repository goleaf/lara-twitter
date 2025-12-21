<?php

namespace App\Http\Controllers;

use App\Http\Requests\Links\LinkRedirectRequest;
use App\Models\Post;
use App\Services\AnalyticsService;
use Illuminate\Http\RedirectResponse;

class LinkRedirectController
{
    public function __invoke(LinkRedirectRequest $request, Post $post, AnalyticsService $analytics): RedirectResponse
    {
        $url = (string) $request->validated('u');

        $post->loadMissing(['user:id,analytics_enabled,is_admin']);
        if ($post->user->analytics_enabled || $post->user->is_admin) {
            $analytics->recordUnique('post_link_click', $post->id);
        }

        return redirect()->away($url);
    }
}
