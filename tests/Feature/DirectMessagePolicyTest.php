<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessagePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dm_policy_none_blocks_new_conversations(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create(['dm_policy' => User::DM_NONE]);

        $this->actingAs($alice)
            ->get(route('messages.new', ['user' => $bob]))
            ->assertForbidden();
    }

    public function test_non_followed_recipient_creates_message_request_when_allowed(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create([
            'dm_policy' => User::DM_EVERYONE,
            'dm_allow_requests' => true,
        ]);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        $this->assertInstanceOf(Conversation::class, $conversation);

        $bobParticipant = $conversation->participants()->where('user_id', $bob->id)->firstOrFail();
        $this->assertTrue($bobParticipant->is_request);
    }

    public function test_recipient_can_accept_request_and_then_send(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create([
            'dm_policy' => User::DM_EVERYONE,
            'dm_allow_requests' => true,
        ]);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        // Bob sees it as a request and cannot send until accepting.
        Livewire::actingAs($bob)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', 'Hi')
            ->call('send')
            ->assertStatus(403);

        Livewire::actingAs($bob)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->call('acceptRequest');

        Livewire::actingAs($bob)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', 'Hi')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $bob->id,
            'body' => 'Hi',
        ]);
    }

    public function test_dm_policy_following_allows_request_when_enabled(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create([
            'dm_policy' => User::DM_FOLLOWING,
            'dm_allow_requests' => true,
        ]);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        $bobParticipant = $conversation->participants()->where('user_id', $bob->id)->firstOrFail();
        $this->assertTrue($bobParticipant->is_request);
    }
}
