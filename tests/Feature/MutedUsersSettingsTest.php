<?php

namespace Tests\Feature;

use App\Models\Mute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MutedUsersSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_unmute_from_settings(): void
    {
        $muter = User::factory()->create(['username' => 'alice']);
        $muted = User::factory()->create(['username' => 'bob']);

        Mute::query()->create([
            'muter_id' => $muter->id,
            'muted_id' => $muted->id,
        ]);

        $this->actingAs($muter);

        Volt::test('profile.muted-users-form')
            ->call('unmute', $muted->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('mutes', [
            'muter_id' => $muter->id,
            'muted_id' => $muted->id,
        ]);
    }
}

