<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
            ->subject('@'.$this->likedBy->username.' liked your post')
            ->line('@'.$this->likedBy->username.' liked your post.')
            ->action('View post', route('posts.show', $this->post->id));

        if ($excerpt !== '') {
            $mail->line('“'.$excerpt.'”');
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_liked',
            'actor_user_id' => $this->likedBy->id,
            'actor_username' => $this->likedBy->username,
            'post_id' => $this->post->id,
            'liked_by_user_id' => $this->likedBy->id,
            'liked_by_username' => $this->likedBy->username,
            'excerpt' => mb_substr($this->post->body, 0, 120),
        ];
    }
}
