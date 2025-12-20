<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_messages(): void
    {
        $this->get(route('messages.index'))->assertRedirect('/login');
    }

    public function test_user_can_start_direct_message_and_send_text(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $this->actingAs($alice)
            ->get(route('messages.new', ['user' => $bob]))
            ->assertRedirect();

        $conversation = Conversation::query()->firstOrFail();

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', 'Hello Bob')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $alice->id,
            'body' => 'Hello Bob',
        ]);
    }

    public function test_user_can_send_message_with_attachments(): void
    {
        Storage::persistentFake('public');

        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', '')
            ->set('attachments', [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
            ])
            ->call('send')
            ->assertHasNoErrors();

        $message = Message::query()->where('conversation_id', $conversation->id)->firstOrFail();

        $this->assertCount(2, $message->attachments);
        Storage::disk('public')->assertExists($message->attachments[0]->path);
        Storage::disk('public')->assertExists($message->attachments[1]->path);
    }

    public function test_user_cannot_view_conversation_they_are_not_in(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $mallory = User::factory()->create();

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        $this->actingAs($mallory)->get(route('messages.show', $conversation))->assertForbidden();
    }
}

