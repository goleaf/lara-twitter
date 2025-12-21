<?php

namespace Tests\Unit\Models;

use App\Models\SpaceReaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_spaceReaction_can_be_created(): void
    {
        $spaceReaction = SpaceReaction::factory()->create();

        $this->assertInstanceOf(SpaceReaction::class, $spaceReaction);
        $this->assertDatabaseHas('space_reactions', [
            'id' => $spaceReaction->id,
        ]);
    }

    public function test_spaceReaction_has_factory(): void
    {
        $spaceReaction = SpaceReaction::factory()->make();

        $this->assertInstanceOf(SpaceReaction::class, $spaceReaction);
    }
}