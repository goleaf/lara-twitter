<?php

namespace Tests\Unit\Models;

use App\Models\MessageAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_messageAttachment_can_be_created(): void
    {
        $messageAttachment = MessageAttachment::factory()->create();

        $this->assertInstanceOf(MessageAttachment::class, $messageAttachment);
        $this->assertDatabaseHas('message_attachments', [
            'id' => $messageAttachment->id,
        ]);
    }

    public function test_messageAttachment_has_factory(): void
    {
        $messageAttachment = MessageAttachment::factory()->make();

        $this->assertInstanceOf(MessageAttachment::class, $messageAttachment);
    }
}