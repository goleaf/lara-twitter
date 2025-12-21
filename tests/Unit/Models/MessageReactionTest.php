<?php

namespace Tests\Unit\Models;

use App\Models\MessageReaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_messageReaction_can_be_created(): void
    {
        $messageReaction = MessageReaction::factory()->create();

        $this->assertInstanceOf(MessageReaction::class, $messageReaction);
        $this->assertDatabaseHas('message_reactions', [
            'id' => $messageReaction->id,
        ]);
    }

    public function test_messageReaction_has_factory(): void
    {
        $messageReaction = MessageReaction::factory()->make();

        $this->assertInstanceOf(MessageReaction::class, $messageReaction);
    }
}