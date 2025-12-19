<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessageReactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_toggle_reaction_on_message(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $bob->following()->attach($alice->id);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', 'Hello')
            ->call('send')
            ->assertHasNoErrors();

        $message = Message::query()->firstOrFail();

        Livewire::actingAs($bob)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->call('toggleReaction', $message->id, '👍')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('message_reactions', [
            'message_id' => $message->id,
            'user_id' => $bob->id,
            'emoji' => '👍',
        ]);

        Livewire::actingAs($bob)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->call('toggleReaction', $message->id, '👍')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('message_reactions', [
            'message_id' => $message->id,
            'user_id' => $bob->id,
            'emoji' => '👍',
        ]);
    }
}

