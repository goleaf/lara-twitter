<?php

namespace Tests\Feature;

use App\Models\Space;
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
}

