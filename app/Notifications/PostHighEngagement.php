<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostHighEngagement extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Post $post,
        public readonly int $likesCount,
        public readonly int $repostsCount,
        public readonly int $repliesCount,
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
        $total = $this->likesCount + $this->repostsCount + $this->repliesCount;
        $excerpt = mb_substr((string) $this->post->body, 0, 120);

        $mail = (new MailMessage)
            ->subject('Your post is getting more attention than usual')
            ->line("Your post is picking up momentum ({$total} engagements).")
            ->action('View post', route('posts.show', $this->post->id));

        if ($excerpt !== '') {
            $mail->line('“'.$excerpt.'”');
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_high_engagement',
            'post_id' => $this->post->id,
            'likes_count' => $this->likesCount,
            'reposts_count' => $this->repostsCount,
            'replies_count' => $this->repliesCount,
            'excerpt' => mb_substr((string) $this->post->body, 0, 120),
        ];
    }
}

