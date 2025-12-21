<?php

namespace Tests\Unit\Models;

use App\Models\SpaceParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceParticipantTest extends TestCase
{
    use RefreshDatabase;

    public function test_spaceParticipant_can_be_created(): void
    {
        $spaceParticipant = SpaceParticipant::factory()->create();

        $this->assertInstanceOf(SpaceParticipant::class, $spaceParticipant);
        $this->assertDatabaseHas('space_participants', [
            'id' => $spaceParticipant->id,
        ]);
    }

    public function test_spaceParticipant_has_factory(): void
    {
        $spaceParticipant = SpaceParticipant::factory()->make();

        $this->assertInstanceOf(SpaceParticipant::class, $spaceParticipant);
    }
}