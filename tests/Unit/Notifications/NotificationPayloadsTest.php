<?php

namespace Tests\Unit\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use App\Models\UserList;
use App\Notifications\AddedToList;
use App\Notifications\FollowedUserPosted;
use App\Notifications\MessageReceived;
use App\Notifications\PostHighEngagement;
use App\Notifications\PostLiked;
use App\Notifications\PostMentioned;
use App\Notifications\PostReposted;
use App\Notifications\PostReplied;
use App\Notifications\UserFollowed;
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

    public function test_post_reposted_notification_payloads(): void
    {
        $reposter = User::factory()->create();
        $original = Post::factory()->create();
        $quote = Post::factory()->for($reposter)->create(['body' => 'Quote text']);
        $notifiable = User::factory()->create();

        $notification = new PostReposted($original, $quote, $reposter, 'quote');

        $channels = $notification->via($notifiable);
        $this->assertSame(['database'], $channels);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('post_reposted', $payload['type']);
        $this->assertSame('quote', $payload['kind']);
        $this->assertSame($original->id, $payload['original_post_id']);
        $this->assertSame($quote->id, $payload['repost_post_id']);
        $this->assertSame($reposter->id, $payload['reposter_user_id']);
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

    public function test_user_followed_notification_payload(): void
    {
        $follower = User::factory()->create();
        $notifiable = User::factory()->create();

        $notification = new UserFollowed($follower);

        $channels = $notification->via($notifiable);
        $this->assertSame(['database'], $channels);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('user_followed', $payload['type']);
        $this->assertSame($follower->id, $payload['follower_user_id']);
        $this->assertSame($follower->username, $payload['follower_username']);
    }

    public function test_added_to_list_notification_payload(): void
    {
        $owner = User::factory()->create();
        $addedBy = User::factory()->create();
        $list = UserList::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Favorites',
        ]);
        $notifiable = User::factory()->create();

        $notification = new AddedToList($list, $addedBy);

        $channels = $notification->via($notifiable);
        $this->assertSame(['database'], $channels);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('added_to_list', $payload['type']);
        $this->assertSame($list->id, $payload['list_id']);
        $this->assertSame('Favorites', $payload['list_name']);
        $this->assertSame($owner->username, $payload['list_owner_username']);
    }

    public function test_message_received_notification_payload(): void
    {
        $sender = User::factory()->create();
        $conversation = Conversation::factory()->create(['created_by_user_id' => $sender->id]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'body' => 'Hello there',
        ]);
        $notifiable = User::factory()->create();

        $notification = new MessageReceived($conversation, $message, $sender);

        $channels = $notification->via($notifiable);
        $this->assertSame(['database'], $channels);

        $payload = $notification->toArray($notifiable);
        $this->assertSame('message_received', $payload['type']);
        $this->assertSame($conversation->id, $payload['conversation_id']);
        $this->assertSame($message->id, $payload['message_id']);
        $this->assertSame($sender->id, $payload['sender_user_id']);
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
