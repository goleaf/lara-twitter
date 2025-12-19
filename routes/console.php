<?php

use App\Models\Post;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('posts:publish-scheduled', function () {
    $now = now();

    $posts = Post::withoutGlobalScopes()
        ->where('is_published', false)
        ->whereNotNull('scheduled_for')
        ->where('scheduled_for', '<=', $now)
        ->orderBy('scheduled_for')
        ->limit(200)
        ->get();

    $count = 0;

    foreach ($posts as $post) {
        $scheduledFor = $post->scheduled_for;

        $post->forceFill([
            'is_published' => true,
            'scheduled_for' => null,
            'created_at' => $scheduledFor ?: $post->created_at,
        ])->save();

        $count++;
    }

    $this->info("Published {$count} scheduled posts.");
})->purpose('Publish due scheduled posts');
