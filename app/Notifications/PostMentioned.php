<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        $channels = ['database'];

        if ($notifiable instanceof User && $notifiable->shouldSendNotificationEmail()) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $excerpt = mb_substr((string) $this->post->body, 0, 120);

        $mail = (new MailMessage)
            ->subject('@'.$this->mentionedBy->username.' mentioned you')
            ->line('@'.$this->mentionedBy->username.' mentioned you in a post.')
            ->action('View post', route('posts.show', $this->post->id));

        if ($excerpt !== '') {
            $mail->line('“'.$excerpt.'”');
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_mentioned',
            'actor_user_id' => $this->mentionedBy->id,
            'actor_username' => $this->mentionedBy->username,
            'post_id' => $this->post->id,
            'mentioned_by_user_id' => $this->mentionedBy->id,
            'mentioned_by_username' => $this->mentionedBy->username,
            'excerpt' => mb_substr($this->post->body, 0, 120),
        ];
    }
}
