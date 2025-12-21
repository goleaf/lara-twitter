<?php

namespace Tests\Unit\Models;

use App\Models\SpaceSpeakerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceSpeakerRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_spaceSpeakerRequest_can_be_created(): void
    {
        $spaceSpeakerRequest = SpaceSpeakerRequest::factory()->create();

        $this->assertInstanceOf(SpaceSpeakerRequest::class, $spaceSpeakerRequest);
        $this->assertDatabaseHas('space_speaker_requests', [
            'id' => $spaceSpeakerRequest->id,
        ]);
    }

    public function test_spaceSpeakerRequest_has_factory(): void
    {
        $spaceSpeakerRequest = SpaceSpeakerRequest::factory()->make();

        $this->assertInstanceOf(SpaceSpeakerRequest::class, $spaceSpeakerRequest);
    }
}