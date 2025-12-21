<?php

namespace Tests\Unit\Models;

use App\Models\Mute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MuteTest extends TestCase
{
    use RefreshDatabase;

    public function test_mute_can_be_created(): void
    {
        $mute = Mute::factory()->create();

        $this->assertInstanceOf(Mute::class, $mute);
        $this->assertDatabaseHas('mutes', [
            'id' => $mute->id,
        ]);
    }

    public function test_mute_has_factory(): void
    {
        $mute = Mute::factory()->make();

        $this->assertInstanceOf(Mute::class, $mute);
    }
}