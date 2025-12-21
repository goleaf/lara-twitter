<?php

namespace Tests\Unit\Models;

use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_can_be_created(): void
    {
        $space = Space::factory()->create();

        $this->assertInstanceOf(Space::class, $space);
        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
        ]);
    }

    public function test_space_has_factory(): void
    {
        $space = Space::factory()->make();

        $this->assertInstanceOf(Space::class, $space);
    }
}