<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserFollowed extends Notification
{
    use Queueable;

    public function __construct(public readonly User $follower)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_followed',
            'actor_user_id' => $this->follower->id,
            'actor_username' => $this->follower->username,
            'follower_user_id' => $this->follower->id,
            'follower_username' => $this->follower->username,
        ];
    }
}

