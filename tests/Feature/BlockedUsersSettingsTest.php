<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class BlockedUsersSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_unblock_from_settings(): void
    {
        $blocker = User::factory()->create(['username' => 'alice']);
        $blocked = User::factory()->create(['username' => 'bob']);

        Block::query()->create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->actingAs($blocker);

        Volt::test('profile.blocked-users-form')
            ->call('unblock', $blocked->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('blocks', [
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);
    }
}

