<?php

namespace Tests\Unit\Models;

use App\Models\Hashtag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HashtagTest extends TestCase
{
    use RefreshDatabase;

    public function test_hashtag_can_be_created(): void
    {
        $hashtag = Hashtag::factory()->create();

        $this->assertInstanceOf(Hashtag::class, $hashtag);
        $this->assertDatabaseHas('hashtags', [
            'id' => $hashtag->id,
        ]);
    }

    public function test_hashtag_has_factory(): void
    {
        $hashtag = Hashtag::factory()->make();

        $this->assertInstanceOf(Hashtag::class, $hashtag);
    }
}