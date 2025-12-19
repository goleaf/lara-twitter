<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostReposted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Post $originalPost,
        public readonly ?Post $repostPost,
        public readonly User $reposter,
        public readonly string $kind, // retweet|quote
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_reposted',
            'kind' => $this->kind,
            'original_post_id' => $this->originalPost->id,
            'repost_post_id' => $this->repostPost?->id,
            'reposter_user_id' => $this->reposter->id,
            'reposter_username' => $this->reposter->username,
            'excerpt' => $this->repostPost ? mb_substr((string) $this->repostPost->body, 0, 120) : null,
        ];
    }
}

