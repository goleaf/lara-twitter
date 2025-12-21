<?php

namespace Tests\Unit\Models;

use App\Models\Moment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MomentTest extends TestCase
{
    use RefreshDatabase;

    public function test_moment_can_be_created(): void
    {
        $moment = Moment::factory()->create();

        $this->assertInstanceOf(Moment::class, $moment);
        $this->assertDatabaseHas('moments', [
            'id' => $moment->id,
        ]);
    }

    public function test_moment_has_factory(): void
    {
        $moment = Moment::factory()->make();

        $this->assertInstanceOf(Moment::class, $moment);
    }
}