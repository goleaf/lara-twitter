<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessageMaxLengthTest extends TestCase
{
    use RefreshDatabase;

    public function test_dm_body_max_is_10000(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $conversation = app(\App\Services\DirectMessageService::class)->findOrCreate($alice, $bob);

        Livewire::actingAs($alice)
            ->test(\App\Livewire\ConversationPage::class, ['conversation' => $conversation])
            ->set('body', str_repeat('a', 10001))
            ->call('send')
            ->assertHasErrors(['body' => 'max']);

        $this->assertDatabaseCount('messages', 0);
    }
}
