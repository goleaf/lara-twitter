<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Notifications\UserFollowed;
use App\Services\FollowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FollowServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggle_throws_when_following_self(): void
    {
        $user = User::factory()->create();
        $service = new FollowService();

        $this->expectException(\InvalidArgumentException::class);
        $service->toggle($user, $user);
    }

    public function test_toggle_follows_and_unfollows_user(): void
    {
        Notification::fake();

        $follower = User::factory()->create();
        $followed = User::factory()->create();

        $service = new FollowService();

        $this->assertTrue($service->toggle($follower, $followed));
        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);
        Notification::assertSentTo($followed, UserFollowed::class);

        $this->assertFalse($service->toggle($follower, $followed));
        $this->assertDatabaseMissing('follows', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);
    }
}
