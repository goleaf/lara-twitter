<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectMessageGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_group_conversation(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);

        $bob->following()->attach($alice->id);
        $carol->following()->attach($alice->id);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\NewConversationPage::class)
            ->set('title', 'Friends')
            ->set('recipientUserIds', [$bob->id, $carol->id])
            ->call('create')
            ->assertRedirect();

        $conversation = Conversation::query()->firstOrFail();

        $this->assertTrue($conversation->is_group);
        $this->assertSame('Friends', $conversation->title);

        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $alice->id,
            'role' => 'admin',
            'is_request' => false,
        ]);

        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $bob->id,
            'role' => 'member',
            'is_request' => false,
        ]);

        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $carol->id,
            'role' => 'member',
            'is_request' => false,
        ]);
    }

    public function test_group_conversation_cannot_include_blocked_users(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);

        $bob->following()->attach($alice->id);
        $carol->following()->attach($alice->id);

        Block::query()->create([
            'blocker_id' => $bob->id,
            'blocked_id' => $carol->id,
        ]);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\NewConversationPage::class)
            ->set('recipientUserIds', [$bob->id, $carol->id])
            ->call('create')
            ->assertStatus(403);

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_group_admin_can_add_and_remove_members(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);
        $dave = User::factory()->create(['username' => 'dave']);

        $conversation = Conversation::query()->create([
            'created_by_user_id' => $alice->id,
            'is_group' => true,
            'title' => null,
        ]);

        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $alice->id,
            'role' => 'admin',
            'is_request' => false,
        ]);

        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $bob->id,
            'role' => 'member',
            'is_request' => false,
        ]);

        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $carol->id,
            'role' => 'member',
            'is_request' => false,
        ]);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('memberUsername', '@dave')
            ->call('addMember')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $dave->id,
        ]);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->call('removeMember', $carol->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $carol->id,
        ]);
    }
}

