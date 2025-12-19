<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostLiked extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Post $post,
        public readonly User $likedBy,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_liked',
            'post_id' => $this->post->id,
            'liked_by_user_id' => $this->likedBy->id,
            'liked_by_username' => $this->likedBy->username,
            'excerpt' => mb_substr($this->post->body, 0, 120),
        ];
    }
}

