<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MessageReceived extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Conversation $conversation,
        public readonly Message $message,
        public readonly User $sender,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'message_received',
            'actor_user_id' => $this->sender->id,
            'actor_username' => $this->sender->username,
            'conversation_id' => $this->conversation->id,
            'message_id' => $this->message->id,
            'sender_user_id' => $this->sender->id,
            'sender_username' => $this->sender->username,
            'excerpt' => $this->message->body ? mb_substr((string) $this->message->body, 0, 120) : null,
        ];
    }
}

