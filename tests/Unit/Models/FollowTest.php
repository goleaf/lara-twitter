<?php

namespace Tests\Unit\Models;

use App\Models\Follow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_can_be_created(): void
    {
        $follow = Follow::factory()->create();

        $this->assertInstanceOf(Follow::class, $follow);
        $this->assertDatabaseHas('follows', [
            'id' => $follow->id,
        ]);
    }

    public function test_follow_has_factory(): void
    {
        $follow = Follow::factory()->make();

        $this->assertInstanceOf(Follow::class, $follow);
    }
}