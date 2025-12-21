<?php

namespace Tests\Unit\Models;

use App\Models\Mention;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mention_can_be_created(): void
    {
        $mention = Mention::factory()->create();

        $this->assertInstanceOf(Mention::class, $mention);
        $this->assertDatabaseHas('mentions', [
            'id' => $mention->id,
        ]);
    }

    public function test_mention_has_factory(): void
    {
        $mention = Mention::factory()->make();

        $this->assertInstanceOf(Mention::class, $mention);
    }
}