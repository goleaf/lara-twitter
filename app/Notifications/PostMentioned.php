<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostMentioned extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Post $post,
        public readonly User $mentionedBy,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_mentioned',
            'post_id' => $this->post->id,
            'mentioned_by_user_id' => $this->mentionedBy->id,
            'mentioned_by_username' => $this->mentionedBy->username,
            'excerpt' => mb_substr($this->post->body, 0, 120),
        ];
    }
}

