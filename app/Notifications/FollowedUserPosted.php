<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowedUserPosted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Post $post,
        public readonly User $author,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'followed_user_posted',
            'actor_user_id' => $this->author->id,
            'actor_username' => $this->author->username,
            'post_id' => $this->post->id,
            'excerpt' => mb_substr((string) $this->post->body, 0, 120),
        ];
    }
}

