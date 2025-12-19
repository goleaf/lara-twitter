<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessageUnsendTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_unsend_recent_message(): void
    {
        Storage::fake('public');

        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $bob->following()->attach($alice->id);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', 'Hello')
            ->set('attachments', [UploadedFile::fake()->image('one.jpg')])
            ->call('send')
            ->assertHasNoErrors();

        $message = Message::query()->firstOrFail();
        $attachment = MessageAttachment::query()->where('message_id', $message->id)->firstOrFail();

        Storage::disk('public')->assertExists($attachment->path);

        Livewire::actingAs($bob)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->call('toggleReaction', $message->id, '👍')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('message_reactions', [
            'message_id' => $message->id,
            'user_id' => $bob->id,
            'emoji' => '👍',
        ]);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->call('unsend', $message->id)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('message_attachments', 0);
        $this->assertDatabaseCount('message_reactions', 0);
        Storage::disk('public')->assertMissing($attachment->path);

        $this->assertSame(0, Message::query()->count());
        $this->assertSame(1, Message::withTrashed()->count());
        $this->assertNotNull(Message::withTrashed()->firstOrFail()->deleted_at);
    }

    public function test_user_cannot_unsend_after_time_window(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

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

        $this->travelTo($now->addMinutes(6));

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->call('unsend', $message->id)
            ->assertStatus(403);

        $this->assertNull($message->fresh()->deleted_at);
    }
}

