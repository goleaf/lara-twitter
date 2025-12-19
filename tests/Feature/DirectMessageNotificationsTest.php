<?php

namespace Tests\Feature;

use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessageNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dm_sends_notification_to_recipient_when_not_request(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        // Make it non-request for Bob so it can notify.
        ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $bob->id)
            ->update(['is_request' => false]);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', 'Hello Bob')
            ->call('send')
            ->assertHasNoErrors();

        $notification = $bob->notifications()->latest()->firstOrFail();
        $this->assertSame('message_received', $notification->data['type'] ?? null);
        $this->assertSame('alice', $notification->data['sender_username'] ?? null);
    }

    public function test_verified_tab_shows_only_verified_actors(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $verified = User::factory()->create(['username' => 'ver', 'is_verified' => true]);
        $unverified = User::factory()->create(['username' => 'unver', 'is_verified' => false]);

        $post = \App\Models\Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello']);

        \App\Models\Like::query()->create(['user_id' => $verified->id, 'post_id' => $post->id]);
        \App\Models\Like::query()->create(['user_id' => $unverified->id, 'post_id' => $post->id]);

        $this->actingAs($alice)
            ->get(route('notifications', ['tab' => 'verified']))
            ->assertOk()
            ->assertSee('ver liked your post')
            ->assertDontSee('unver liked your post');
    }
}
