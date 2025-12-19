<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessageTypingIndicatorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_typing_indicator_shows_other_user_typing(): void
    {
        Cache::flush();

        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $bob->following()->attach($alice->id);

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        Livewire::actingAs($bob)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->call('typing')
            ->assertHasNoErrors();

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->assertSee('typing');
    }
}

