<?php

namespace Tests\Unit\Notifications;

use App\Models\Post;
use App\Models\User;
use App\Notifications\FollowedUserPosted;
use App\Notifications\PostHighEngagement;
use App\Notifications\PostLiked;
use App\Notifications\PostMentioned;
use App\Notifications\PostReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class NotificationPayloadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_liked_notification_payloads(): void
    {
        $actor = User::factory()->create();
        $post = Post::factory()->for($actor)->create();
        $notifiable = $this->makeEmailNotifiable();

        $notification = new PostLiked($post, $actor);

        $channels = $notification->via($notifiable);
        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);

        $mail = $notification->toMail($notifiable);
        $this->assertInstanceOf(MailMessage::class, $mail);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('post_liked', $payload['type']);
        $this->assertSame($post->id, $payload['post_id']);
    }

    public function test_post_mentioned_notification_payloads(): void
    {
        $actor = User::factory()->create();
        $post = Post::factory()->for($actor)->create();
        $notifiable = $this->makeEmailNotifiable();

        $notification = new PostMentioned($post, $actor);

        $channels = $notification->via($notifiable);
        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);

        $mail = $notification->toMail($notifiable);
        $this->assertInstanceOf(MailMessage::class, $mail);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('post_mentioned', $payload['type']);
        $this->assertSame($post->id, $payload['post_id']);
    }

    public function test_post_replied_notification_payloads(): void
    {
        $replier = User::factory()->create();
        $original = Post::factory()->create();
        $reply = Post::factory()->for($replier)->create();
        $notifiable = $this->makeEmailNotifiable();

        $notification = new PostReplied($original, $reply, $replier);

        $channels = $notification->via($notifiable);
        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);

        $mail = $notification->toMail($notifiable);
        $this->assertInstanceOf(MailMessage::class, $mail);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('post_replied', $payload['type']);
        $this->assertSame($original->id, $payload['original_post_id']);
        $this->assertSame($reply->id, $payload['reply_post_id']);
    }

    public function test_post_high_engagement_notification_payloads(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $notifiable = $this->makeEmailNotifiable();

        $notification = new PostHighEngagement($post, 12, 3, 4);

        $channels = $notification->via($notifiable);
        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);

        $mail = $notification->toMail($notifiable);
        $this->assertInstanceOf(MailMessage::class, $mail);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('post_high_engagement', $payload['type']);
        $this->assertSame(12, $payload['likes_count']);
        $this->assertSame(3, $payload['reposts_count']);
        $this->assertSame(4, $payload['replies_count']);
    }

    public function test_followed_user_posted_notification_payload(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $notifiable = User::factory()->create();

        $notification = new FollowedUserPosted($post, $author);

        $channels = $notification->via($notifiable);
        $this->assertSame(['database'], $channels);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('followed_user_posted', $payload['type']);
        $this->assertSame($post->id, $payload['post_id']);
    }

    private function makeEmailNotifiable(): User
    {
        return User::factory()->create([
            'notification_settings' => [
                'email_enabled' => true,
                'quiet_hours_enabled' => false,
            ],
        ]);
    }
}
