<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_can_be_created(): void
    {
        $conversation = Conversation::factory()->create();

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
        ]);
    }

    public function test_conversation_has_factory(): void
    {
        $conversation = Conversation::factory()->make();

        $this->assertInstanceOf(Conversation::class, $conversation);
    }
}