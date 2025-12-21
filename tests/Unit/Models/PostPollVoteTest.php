<?php

namespace Tests\Unit\Models;

use App\Models\PostPollVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPollVoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_postPollVote_can_be_created(): void
    {
        $postPollVote = PostPollVote::factory()->create();

        $this->assertInstanceOf(PostPollVote::class, $postPollVote);
        $this->assertDatabaseHas('post_poll_votes', [
            'id' => $postPollVote->id,
        ]);
    }

    public function test_postPollVote_has_factory(): void
    {
        $postPollVote = PostPollVote::factory()->make();

        $this->assertInstanceOf(PostPollVote::class, $postPollVote);
    }
}