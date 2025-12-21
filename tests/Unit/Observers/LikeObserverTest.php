<?php

namespace Tests\Unit\Observers;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostHighEngagement;
use App\Notifications\PostLiked;
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
}
