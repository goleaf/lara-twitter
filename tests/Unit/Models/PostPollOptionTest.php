<?php

namespace Tests\Unit\Models;

use App\Models\PostPollOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPollOptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_postPollOption_can_be_created(): void
    {
        $postPollOption = PostPollOption::factory()->create();

        $this->assertInstanceOf(PostPollOption::class, $postPollOption);
        $this->assertDatabaseHas('post_poll_options', [
            'id' => $postPollOption->id,
        ]);
    }

    public function test_postPollOption_has_factory(): void
    {
        $postPollOption = PostPollOption::factory()->make();

        $this->assertInstanceOf(PostPollOption::class, $postPollOption);
    }
}