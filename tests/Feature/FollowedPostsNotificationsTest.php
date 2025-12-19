<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Notifications\FollowedUserPosted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FollowedPostsNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_post_notifies_followers_when_enabled(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $follower = User::factory()->create([
            'username' => 'bob',
            'notification_settings' => ['followed_posts' => true],
        ]);

        $follower->following()->attach($author->id);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        Notification::assertSentTo(
            $follower,
            FollowedUserPosted::class,
            fn (FollowedUserPosted $n) => $n->post->is($post) && $n->author->is($author),
        );
    }

    public function test_new_post_does_not_notify_followers_when_disabled(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $follower = User::factory()->create([
            'username' => 'bob',
            'notification_settings' => ['followed_posts' => false],
        ]);

        $follower->following()->attach($author->id);

        Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        Notification::assertNotSentTo($follower, FollowedUserPosted::class);
    }
}

