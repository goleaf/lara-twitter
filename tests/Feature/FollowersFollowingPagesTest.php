<?php

namespace Tests\Feature;

use App\Livewire\FollowersPage;
use App\Livewire\FollowingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FollowersFollowingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_followers_page_lists_followers_and_owner_can_remove(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $follower = User::factory()->create(['username' => 'bob']);

        $follower->following()->attach($owner->id);

        Livewire::actingAs($owner)
            ->test(FollowersPage::class, ['user' => $owner])
            ->assertSee('@bob')
            ->call('removeFollower', $follower->id);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $follower->id,
            'followed_id' => $owner->id,
        ]);
    }

    public function test_following_page_lists_followed_users(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $followed = User::factory()->create(['username' => 'alice']);

        $owner->following()->attach($followed->id);

        Livewire::test(FollowingPage::class, ['user' => $owner])
            ->assertSee('@alice');
    }
}

