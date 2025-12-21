<?php

namespace Tests\Unit\Models;

use App\Models\MutedTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MutedTermTest extends TestCase
{
    use RefreshDatabase;

    public function test_mutedTerm_can_be_created(): void
    {
        $mutedTerm = MutedTerm::factory()->create();

        $this->assertInstanceOf(MutedTerm::class, $mutedTerm);
        $this->assertDatabaseHas('muted_terms', [
            'id' => $mutedTerm->id,
        ]);
    }

    public function test_mutedTerm_has_factory(): void
    {
        $mutedTerm = MutedTerm::factory()->make();

        $this->assertInstanceOf(MutedTerm::class, $mutedTerm);
    }
}