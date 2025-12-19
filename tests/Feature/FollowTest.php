<?php

namespace Tests\Feature;

use App\Livewire\ProfilePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_and_unfollow_from_profile_page(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        Livewire::actingAs($alice)
            ->test(ProfilePage::class, ['user' => $bob])
            ->call('toggleFollow');

        $this->assertDatabaseHas('follows', [
            'follower_id' => $alice->id,
            'followed_id' => $bob->id,
        ]);

        Livewire::actingAs($alice)
            ->test(ProfilePage::class, ['user' => $bob])
            ->call('toggleFollow');

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $alice->id,
            'followed_id' => $bob->id,
        ]);
    }

    public function test_guest_cannot_follow(): void
    {
        $bob = User::factory()->create(['username' => 'bob']);

        Livewire::test(ProfilePage::class, ['user' => $bob])
            ->call('toggleFollow')
            ->assertStatus(403);
    }
}
