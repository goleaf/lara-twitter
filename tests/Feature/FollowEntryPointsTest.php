<?php

namespace Tests\Feature;

use App\Livewire\ExplorePage;
use App\Livewire\FollowersPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FollowEntryPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_from_explore_page_sends_notification_to_followed_user(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer']);
        $target = User::factory()->create(['username' => 'target']);

        Livewire::actingAs($viewer)
            ->test(ExplorePage::class)
            ->call('toggleFollow', $target->id);

        $notification = $target->notifications()->latest()->firstOrFail();
        $this->assertSame('user_followed', $notification->data['type'] ?? null);
        $this->assertSame('viewer', $notification->data['follower_username'] ?? null);
    }

    public function test_follow_from_followers_page_sends_notification_to_followed_user(): void
    {
        $profileUser = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $bob->following()->attach($profileUser->id);

        $carol = User::factory()->create(['username' => 'carol']);

        Livewire::actingAs($carol)
            ->test(FollowersPage::class, ['user' => $profileUser])
            ->call('toggleFollow', $bob->id);

        $notification = $bob->notifications()->latest()->firstOrFail();
        $this->assertSame('user_followed', $notification->data['type'] ?? null);
        $this->assertSame('carol', $notification->data['follower_username'] ?? null);
    }
}
