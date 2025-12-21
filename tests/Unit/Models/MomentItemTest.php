<?php

namespace Tests\Unit\Models;

use App\Models\MomentItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MomentItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_momentItem_can_be_created(): void
    {
        $momentItem = MomentItem::factory()->create();

        $this->assertInstanceOf(MomentItem::class, $momentItem);
        $this->assertDatabaseHas('moment_items', [
            'id' => $momentItem->id,
        ]);
    }

    public function test_momentItem_has_factory(): void
    {
        $momentItem = MomentItem::factory()->make();

        $this->assertInstanceOf(MomentItem::class, $momentItem);
    }
}