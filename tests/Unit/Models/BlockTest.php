<?php

namespace Tests\Unit\Models;

use App\Models\Block;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_block_can_be_created(): void
    {
        $block = Block::factory()->create();

        $this->assertInstanceOf(Block::class, $block);
        $this->assertDatabaseHas('blocks', [
            'blocker_id' => $block->blocker_id,
            'blocked_id' => $block->blocked_id,
        ]);
    }

    public function test_block_has_factory(): void
    {
        $block = Block::factory()->make();

        $this->assertInstanceOf(Block::class, $block);
    }
}
