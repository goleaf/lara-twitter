<?php

namespace Tests\Feature;

use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessageReadReceiptsTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_receipt_shows_seen_when_enabled_and_read(): void
    {
        $alice = User::factory()->create(['username' => 'alice', 'dm_read_receipts' => true]);
        $bob = User::factory()->create(['username' => 'bob', 'dm_read_receipts' => true]);

        $bob->following()->attach($alice->id);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        $now = now();
        $this->travelTo($now);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', 'Hello')
            ->call('send')
            ->assertHasNoErrors();

        $message = Message::query()->firstOrFail();

        ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $bob->id)
            ->update(['last_read_at' => $message->created_at->copy()->addSecond()]);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->assertSee('Seen');
    }

    public function test_read_receipt_is_hidden_when_recipient_disables_read_receipts(): void
    {
        $alice = User::factory()->create(['username' => 'alice', 'dm_read_receipts' => true]);
        $bob = User::factory()->create(['username' => 'bob', 'dm_read_receipts' => false]);

        $bob->following()->attach($alice->id);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', 'Hello')
            ->call('send')
            ->assertHasNoErrors();

        $message = Message::query()->firstOrFail();

        ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $bob->id)
            ->update(['last_read_at' => $message->created_at->copy()->addSecond()]);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->assertDontSee('Seen');
    }
}

