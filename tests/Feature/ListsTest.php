<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_list_feed_supports_large_member_counts(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);

        $list = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Big list',
            'is_private' => false,
        ]);

        $count = 1200;
        $batchSize = 200;

        $buffer = [];
        for ($i = 0; $i < $count; $i++) {
            $buffer[] = [
                'name' => 'Member '.$i,
                'username' => 'bulk_'.$i,
                'email' => 'bulk_'.$i.'@example.com',
                'password' => 'password',
            ];

            if (count($buffer) >= $batchSize) {
                DB::table('users')->insert($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table('users')->insert($buffer);
        }

        $memberIds = DB::table('users')->where('username', 'like', 'bulk_%')->pluck('id');

        foreach ($memberIds->chunk(400) as $chunk) {
            DB::table('user_list_user')->insert(
                $chunk->map(fn (int $id) => ['user_list_id' => $list->id, 'user_id' => $id])->all()
            );
        }

        Livewire::test(\App\Livewire\ListPage::class, ['list' => $list])
            ->assertStatus(200);
    }
}
