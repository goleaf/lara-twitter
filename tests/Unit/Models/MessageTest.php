<?php

namespace Tests\Unit\Models;

use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_can_be_created(): void
    {
        $message = Message::factory()->create();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
        ]);
    }

    public function test_message_has_factory(): void
    {
        $message = Message::factory()->make();

        $this->assertInstanceOf(Message::class, $message);
    }
}