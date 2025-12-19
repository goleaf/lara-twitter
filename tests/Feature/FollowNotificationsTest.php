<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FollowNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_sends_notification_to_followed_user(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        Livewire::actingAs($bob)
            ->test(\App\Livewire\ProfilePage::class, ['user' => $alice])
            ->call('toggleFollow');

        $notification = $alice->notifications()->latest()->firstOrFail();
        $this->assertSame('user_followed', $notification->data['type'] ?? null);
        $this->assertSame('bob', $notification->data['follower_username'] ?? null);
    }

    public function test_follow_notification_respects_only_following_filter(): void
    {
        $alice = User::factory()->create([
            'username' => 'alice',
            'notification_settings' => ['only_following' => true, 'follows' => true],
        ]);

        $bob = User::factory()->create(['username' => 'bob']);

        Livewire::actingAs($bob)
            ->test(\App\Livewire\ProfilePage::class, ['user' => $alice])
            ->call('toggleFollow');

        $this->assertDatabaseCount('notifications', 0);

        // Alice follows Bob -> now Bob is "followed by" Alice? Actually filter is "accounts you follow".
        // Make Alice follow Bob so Alice "follows" Bob, then Bob follows Alice again shouldn't be possible, so simulate with Carol.
        $alice->following()->attach($bob->id);

        $carol = User::factory()->create(['username' => 'carol']);
        $alice->following()->attach($carol->id);

        Livewire::actingAs($carol)
            ->test(\App\Livewire\ProfilePage::class, ['user' => $alice])
            ->call('toggleFollow');

        $notification = $alice->notifications()->latest()->firstOrFail();
        $this->assertSame('user_followed', $notification->data['type'] ?? null);
        $this->assertSame('carol', $notification->data['follower_username'] ?? null);
    }
}
