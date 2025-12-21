<?php

namespace Tests\Unit\Models;

use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_can_be_created(): void
    {
        $like = Like::factory()->create();

        $this->assertInstanceOf(Like::class, $like);
        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
        ]);
    }

    public function test_like_has_factory(): void
    {
        $like = Like::factory()->make();

        $this->assertInstanceOf(Like::class, $like);
    }
}