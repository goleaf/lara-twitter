<?php

namespace Tests\Unit\Observers;

use App\Models\Block;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostHighEngagement;
use App\Notifications\PostLiked;
use App\Observers\LikeObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LikeObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_observer_skips_self_like_notifications(): void
    {
        Notification::fake();

        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        Like::factory()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
        ]);

        Notification::assertNothingSent();
    }

    public function test_like_observer_sends_post_liked_notification(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['likes' => true],
        ]);
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        Like::factory()->create([
            'post_id' => $post->id,
            'user_id' => $liker->id,
        ]);

        Notification::assertSentTo($author, PostLiked::class);
    }

    public function test_like_observer_skips_post_liked_when_blocked(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['likes' => true],
        ]);
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        Block::factory()->create([
            'blocker_id' => $author->id,
            'blocked_id' => $liker->id,
        ]);

        Like::factory()->create([
            'post_id' => $post->id,
            'user_id' => $liker->id,
        ]);

        Notification::assertNotSentTo($author, PostLiked::class);
    }

    public function test_like_observer_skips_post_liked_when_notifications_disabled(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['likes' => false],
        ]);
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        Like::factory()->create([
            'post_id' => $post->id,
            'user_id' => $liker->id,
        ]);

        Notification::assertNotSentTo($author, PostLiked::class);
    }

    public function test_like_observer_sends_high_engagement_notification(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['high_engagement' => true, 'likes' => true],
        ]);
        $post = Post::factory()->for($author)->create([
            'created_at' => now()->subHour(),
        ]);

        $likers = User::factory()->count(5)->create();

        foreach ($likers as $liker) {
            Like::factory()->create([
                'post_id' => $post->id,
                'user_id' => $liker->id,
            ]);
        }

        Notification::assertSentTo($author, PostHighEngagement::class);
        $this->assertNotNull($post->fresh()->high_engagement_notified_at);
    }

    public function test_like_observer_skips_high_engagement_when_already_notified(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['high_engagement' => true],
        ]);
        $post = Post::factory()->for($author)->create([
            'created_at' => now()->subHour(),
            'high_engagement_notified_at' => now(),
        ]);

        Like::factory()->create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
        ]);

        Notification::assertNotSentTo($author, PostHighEngagement::class);
    }

    public function test_like_observer_skips_high_engagement_when_disabled(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['high_engagement' => false],
        ]);
        $post = Post::factory()->for($author)->create([
            'created_at' => now()->subHour(),
        ]);

        Like::factory()->create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
        ]);

        Notification::assertNotSentTo($author, PostHighEngagement::class);
    }

    public function test_like_observer_skips_high_engagement_below_threshold(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['high_engagement' => true],
        ]);
        $post = Post::factory()->for($author)->create([
            'created_at' => now()->subHour(),
        ]);

        User::factory()->count(4)->create()->each(function (User $liker) use ($post): void {
            Like::factory()->create([
                'post_id' => $post->id,
                'user_id' => $liker->id,
            ]);
        });

        Notification::assertNotSentTo($author, PostHighEngagement::class);
    }

    public function test_like_observer_skips_high_engagement_for_old_post(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['high_engagement' => true],
        ]);
        $post = Post::factory()->for($author)->create([
            'created_at' => now()->subDays(2),
        ]);

        $likers = User::factory()->count(5)->create();

        foreach ($likers as $liker) {
            Like::factory()->create([
                'post_id' => $post->id,
                'user_id' => $liker->id,
            ]);
        }

        Notification::assertNotSentTo($author, PostHighEngagement::class);
    }

    public function test_like_observer_skips_high_engagement_when_update_fails(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['high_engagement' => true],
        ]);
        $post = Post::factory()->for($author)->create([
            'created_at' => now()->subHour(),
        ]);

        User::factory()->count(5)->create()->each(function (User $liker) use ($post): void {
            Like::withoutEvents(function () use ($post, $liker): void {
                Like::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $liker->id,
                ]);
            });
        });

        Post::query()->whereKey($post->id)->update(['high_engagement_notified_at' => now()]);

        $observer = new LikeObserver();
        $method = new \ReflectionMethod($observer, 'maybeNotifyHighEngagement');
        $method->setAccessible(true);
        $method->invoke($observer, $post);

        Notification::assertNotSentTo($author, PostHighEngagement::class);
    }
}
