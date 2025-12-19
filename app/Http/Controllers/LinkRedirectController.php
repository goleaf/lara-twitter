<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\AnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LinkRedirectController
{
    public function __invoke(Request $request, Post $post, AnalyticsService $analytics): RedirectResponse
    {
        $url = $request->query('u');
        if (! is_string($url) || $url === '') {
            abort(404);
        }

        if (! $this->isSafeOutboundUrl($url)) {
            abort(400);
        }

        $post->loadMissing('user');
        if ($post->user->analytics_enabled || $post->user->is_admin) {
            $analytics->recordUnique('post_link_click', $post->id);
        }

        return redirect()->away($url);
    }

    private function isSafeOutboundUrl(string $url): bool
    {
        if (strlen($url) > 2048) {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        return true;
    }
}

