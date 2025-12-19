<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\UserList;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AddedToList extends Notification
{
    use Queueable;

    public function __construct(
        public readonly UserList $list,
        public readonly User $addedBy,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'added_to_list',
            'actor_user_id' => $this->addedBy->id,
            'actor_username' => $this->addedBy->username,
            'list_id' => $this->list->id,
            'list_name' => $this->list->name,
            'list_owner_username' => $this->list->owner?->username,
        ];
    }
}

