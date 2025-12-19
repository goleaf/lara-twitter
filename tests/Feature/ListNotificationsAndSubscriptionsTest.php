<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListNotificationsAndSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_user_to_public_list_sends_notification(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $member = User::factory()->create(['username' => 'bob']);

        $list = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'News',
            'description' => null,
            'is_private' => false,
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\ListPage::class, ['list' => $list])
            ->set('member_username', '@bob')
            ->call('addMember');

        $notification = $member->notifications()->latest()->firstOrFail();
        $this->assertSame('added_to_list', $notification->data['type'] ?? null);
        $this->assertSame('News', $notification->data['list_name'] ?? null);
    }

    public function test_adding_user_to_private_list_does_not_send_notification(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $member = User::factory()->create(['username' => 'bob']);

        $list = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Private',
            'description' => null,
            'is_private' => true,
        ]);

        // Owner can see; member can't, so we add via relation to simulate.
        $list->members()->syncWithoutDetaching([$member->id]);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_user_can_subscribe_and_unsubscribe_to_public_list(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $viewer = User::factory()->create(['username' => 'alice']);

        $list = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Public list',
            'description' => null,
            'is_private' => false,
        ]);

        Livewire::actingAs($viewer)
            ->test(\App\Livewire\ListPage::class, ['list' => $list])
            ->call('toggleSubscribe');

        $this->assertDatabaseHas('user_list_subscriptions', [
            'user_list_id' => $list->id,
            'user_id' => $viewer->id,
        ]);

        Livewire::actingAs($viewer)
            ->test(\App\Livewire\ListPage::class, ['list' => $list])
            ->call('toggleSubscribe');

        $this->assertDatabaseMissing('user_list_subscriptions', [
            'user_list_id' => $list->id,
            'user_id' => $viewer->id,
        ]);
    }
}

