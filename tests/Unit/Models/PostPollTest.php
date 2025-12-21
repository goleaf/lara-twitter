<?php

namespace Tests\Unit\Models;

use App\Models\PostPoll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPollTest extends TestCase
{
    use RefreshDatabase;

    public function test_postPoll_can_be_created(): void
    {
        $postPoll = PostPoll::factory()->create();

        $this->assertInstanceOf(PostPoll::class, $postPoll);
        $this->assertDatabaseHas('post_polls', [
            'id' => $postPoll->id,
        ]);
    }

    public function test_postPoll_has_factory(): void
    {
        $postPoll = PostPoll::factory()->make();

        $this->assertInstanceOf(PostPoll::class, $postPoll);
    }
}