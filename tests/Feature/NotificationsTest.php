<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Notifications\PostLiked;
use App\Notifications\PostReposted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_page_requires_auth(): void
    {
        $this->get(route('notifications'))->assertRedirect('/login');
    }

    public function test_like_sends_notification_to_post_author(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $liker = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        $post->likes()->create(['user_id' => $liker->id]);

        Notification::assertSentTo(
            $author,
            PostLiked::class,
            fn (PostLiked $n) => $n->post->is($post) && $n->likedBy->is($liker),
        );
    }

    public function test_like_does_not_notify_when_disabled(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice', 'notification_settings' => ['likes' => false]]);
        $liker = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        $post->likes()->create(['user_id' => $liker->id]);

        Notification::assertNotSentTo($author, PostLiked::class);
    }

    public function test_repost_sends_notification_to_post_author(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $reposter = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        $repost = Post::query()->create([
            'user_id' => $reposter->id,
            'repost_of_id' => $post->id,
            'body' => '',
        ]);

        Notification::assertSentTo(
            $author,
            PostReposted::class,
            fn (PostReposted $n) => $n->originalPost->is($post)
                && $n->repostPost?->is($repost)
                && $n->reposter->is($reposter)
                && $n->kind === 'retweet',
        );
    }

    public function test_notifications_page_renders_database_notifications(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $liker = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);
        $post->likes()->create(['user_id' => $liker->id]);

        $response = $this->actingAs($author)->get(route('notifications'));

        $response
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('bob')
            ->assertSee('liked your post');
    }
}
