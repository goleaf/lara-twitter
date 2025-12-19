<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostReplied extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Post $originalPost,
        public readonly Post $replyPost,
        public readonly User $replier,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_replied',
            'actor_user_id' => $this->replier->id,
            'actor_username' => $this->replier->username,
            'original_post_id' => $this->originalPost->id,
            'reply_post_id' => $this->replyPost->id,
            'replier_user_id' => $this->replier->id,
            'replier_username' => $this->replier->username,
            'excerpt' => mb_substr($this->replyPost->body, 0, 120),
        ];
    }
}
