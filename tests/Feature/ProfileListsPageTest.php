<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileListsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_lists_page_shows_public_lists_that_include_user(): void
    {
        $target = User::factory()->create(['username' => 'alice']);
        $owner = User::factory()->create(['username' => 'owner']);

        $publicList = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Public list',
            'is_private' => false,
        ]);

        $publicList->members()->attach($target->id);

        $privateList = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Private list',
            'is_private' => true,
        ]);

        $privateList->members()->attach($target->id);

        $this->get(route('profile.lists', ['user' => $target]))
            ->assertOk()
            ->assertSee('Public list')
            ->assertDontSee('Private list');
    }
}

