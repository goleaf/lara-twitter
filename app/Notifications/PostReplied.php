<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        $channels = ['database'];

        if ($notifiable instanceof User && $notifiable->shouldSendNotificationEmail()) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $excerpt = mb_substr((string) $this->replyPost->body, 0, 120);

        $mail = (new MailMessage)
            ->subject('@'.$this->replier->username.' replied to your post')
            ->line('@'.$this->replier->username.' replied to your post.')
            ->action('View reply', route('posts.show', $this->replyPost->id));

        if ($excerpt !== '') {
            $mail->line('“'.$excerpt.'”');
        }

        return $mail;
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
