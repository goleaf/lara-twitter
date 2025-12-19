<?php

namespace Tests\Feature;

use App\Models\Space;
use App\Models\SpaceParticipant;
use App\Models\SpaceSpeakerRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SpacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_spaces_index_is_public(): void
    {
        $this->get(route('spaces.index'))->assertOk()->assertSee('Spaces');
    }

    public function test_user_can_create_space_and_join_leave(): void
    {
        $host = User::factory()->create(['username' => 'host']);

        Livewire::actingAs($host)
            ->test(\App\Livewire\SpacesPage::class)
            ->set('title', 'My Space')
            ->set('description', 'Hello')
            ->call('create')
            ->assertRedirect();

        $space = Space::query()->firstOrFail();

        Livewire::actingAs($host)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('join')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('space_participants', [
            'space_id' => $space->id,
            'user_id' => $host->id,
            'role' => 'host',
        ]);

        Livewire::actingAs($host)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('leave')
            ->assertHasNoErrors();
    }

    public function test_host_can_start_and_end_space(): void
    {
        $host = User::factory()->create();

        $space = Space::query()->create([
            'host_user_id' => $host->id,
            'title' => 'Space',
        ]);

        Livewire::actingAs($host)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('start')
            ->assertHasNoErrors();

        $this->assertNotNull($space->refresh()->started_at);

        Livewire::actingAs($host)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('end')
            ->assertHasNoErrors();

        $this->assertNotNull($space->refresh()->ended_at);
    }

    public function test_listener_can_request_to_speak_and_host_can_approve(): void
    {
        $host = User::factory()->create(['username' => 'host']);
        $listener = User::factory()->create(['username' => 'listener']);

        $space = Space::query()->create([
            'host_user_id' => $host->id,
            'title' => 'Space',
        ]);

        Livewire::actingAs($listener)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('join')
            ->call('requestToSpeak')
            ->assertHasNoErrors();

        $request = SpaceSpeakerRequest::query()->firstOrFail();

        Livewire::actingAs($host)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('decideSpeakerRequest', $request->id, 'approve')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('space_participants', [
            'space_id' => $space->id,
            'user_id' => $listener->id,
            'role' => 'speaker',
        ]);

        $this->assertDatabaseHas('space_speaker_requests', [
            'id' => $request->id,
            'status' => SpaceSpeakerRequest::STATUS_APPROVED,
        ]);
    }

    public function test_cohost_can_moderate_speaker_requests(): void
    {
        $host = User::factory()->create(['username' => 'host']);
        $cohost = User::factory()->create(['username' => 'cohost']);
        $listener = User::factory()->create(['username' => 'listener']);

        $space = Space::query()->create([
            'host_user_id' => $host->id,
            'title' => 'Space',
        ]);

        SpaceParticipant::query()->create([
            'space_id' => $space->id,
            'user_id' => $cohost->id,
            'role' => 'cohost',
            'joined_at' => now(),
        ]);

        Livewire::actingAs($listener)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('join')
            ->call('requestToSpeak');

        $request = SpaceSpeakerRequest::query()->firstOrFail();

        Livewire::actingAs($cohost)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('decideSpeakerRequest', $request->id, 'approve')
            ->assertHasNoErrors();
    }

    public function test_space_enforces_max_speaker_limit(): void
    {
        $host = User::factory()->create(['username' => 'host']);
        $space = Space::query()->create([
            'host_user_id' => $host->id,
            'title' => 'Space',
        ]);

        // Host counts as 1 speaker; add 12 more speakers.
        $speakers = User::factory()->count(12)->create();
        foreach ($speakers as $u) {
            SpaceParticipant::query()->create([
                'space_id' => $space->id,
                'user_id' => $u->id,
                'role' => 'speaker',
                'joined_at' => now(),
            ]);
        }

        $listener = User::factory()->create(['username' => 'listener']);
        Livewire::actingAs($listener)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('join')
            ->call('requestToSpeak');

        $request = SpaceSpeakerRequest::query()->firstOrFail();

        Livewire::actingAs($host)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('decideSpeakerRequest', $request->id, 'approve')
            ->assertStatus(422);
    }

    public function test_space_sets_recording_expiry_when_enabled_and_ended(): void
    {
        $host = User::factory()->create();

        $space = Space::query()->create([
            'host_user_id' => $host->id,
            'title' => 'Space',
            'recording_enabled' => true,
        ]);

        $now = now();
        $this->travelTo($now);

        Livewire::actingAs($host)
            ->test(\App\Livewire\SpacePage::class, ['space' => $space])
            ->call('end')
            ->assertHasNoErrors();

        $this->assertEquals(
            $now->addDays(30)->toDateTimeString(),
            $space->refresh()->recording_available_until->toDateTimeString(),
        );
    }
}
