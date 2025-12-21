<?php

namespace Tests\Unit\Models;

use App\Models\ConversationParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationParticipantTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversationParticipant_can_be_created(): void
    {
        $conversationParticipant = ConversationParticipant::factory()->create();

        $this->assertInstanceOf(ConversationParticipant::class, $conversationParticipant);
        $this->assertDatabaseHas('conversation_participants', [
            'id' => $conversationParticipant->id,
        ]);
    }

    public function test_conversationParticipant_has_factory(): void
    {
        $conversationParticipant = ConversationParticipant::factory()->make();

        $this->assertInstanceOf(ConversationParticipant::class, $conversationParticipant);
    }
}