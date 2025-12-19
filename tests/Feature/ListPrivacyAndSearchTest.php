<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListPrivacyAndSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_list_is_only_visible_to_owner(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $member = User::factory()->create(['username' => 'member']);

        $list = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Private',
            'description' => null,
            'is_private' => true,
        ]);

        $list->members()->syncWithoutDetaching([$member->id]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\ListPage::class, ['list' => $list])
            ->assertStatus(200);

        Livewire::actingAs($member)
            ->test(\App\Livewire\ListPage::class, ['list' => $list])
            ->assertStatus(403);

        Livewire::test(\App\Livewire\ListPage::class, ['list' => $list])
            ->assertStatus(403);
    }

    public function test_search_shows_only_public_lists(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);

        UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Newsroom',
            'description' => 'Public list',
            'is_private' => false,
        ]);

        UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Secret Newsroom',
            'description' => 'Private list',
            'is_private' => true,
        ]);

        $this->get(route('search', ['type' => 'lists', 'q' => 'newsroom']))
            ->assertOk()
            ->assertSee('Newsroom')
            ->assertDontSee('Secret Newsroom');
    }
}
