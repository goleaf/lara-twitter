<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_list_and_add_member(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $member = User::factory()->create(['username' => 'member']);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\ListsPage::class)
            ->set('name', 'My List')
            ->set('description', 'Tech people')
            ->set('is_private', true)
            ->call('create')
            ->assertHasNoErrors();

        $list = UserList::query()->firstOrFail();
        $this->assertSame($owner->id, $list->owner_id);
        $this->assertTrue($list->is_private);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\ListPage::class, ['list' => $list])
            ->set('member_username', '@member')
            ->call('addMember')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('user_list_user', [
            'user_list_id' => $list->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_list_feed_shows_posts_from_members_only(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $member = User::factory()->create(['username' => 'member']);
        $other = User::factory()->create(['username' => 'other']);

        $list = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'My List',
            'is_private' => false,
        ]);

        $list->members()->attach($member->id);

        Post::query()->create(['user_id' => $member->id, 'body' => 'From member']);
        Post::query()->create(['user_id' => $other->id, 'body' => 'From other']);

        $response = $this->get(route('lists.show', $list));

        $response
            ->assertOk()
            ->assertSee('From member')
            ->assertDontSee('From other');
    }

    public function test_private_list_is_only_visible_to_owner(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $member = User::factory()->create(['username' => 'member']);
        $outsider = User::factory()->create(['username' => 'outsider']);

        $list = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Private',
            'is_private' => true,
        ]);

        $list->members()->attach($member->id);

        Livewire::test(\App\Livewire\ListPage::class, ['list' => $list])->assertStatus(403);
        Livewire::actingAs($outsider)->test(\App\Livewire\ListPage::class, ['list' => $list])->assertStatus(403);
        Livewire::actingAs($member)->test(\App\Livewire\ListPage::class, ['list' => $list])->assertStatus(403);
        Livewire::actingAs($owner)->test(\App\Livewire\ListPage::class, ['list' => $list])->assertStatus(200);
    }
}
