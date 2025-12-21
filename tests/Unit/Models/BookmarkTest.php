<?php

namespace Tests\Unit\Models;

use App\Models\Bookmark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookmark_can_be_created(): void
    {
        $bookmark = Bookmark::factory()->create();

        $this->assertInstanceOf(Bookmark::class, $bookmark);
        $this->assertDatabaseHas('bookmarks', [
            'id' => $bookmark->id,
        ]);
    }

    public function test_bookmark_has_factory(): void
    {
        $bookmark = Bookmark::factory()->make();

        $this->assertInstanceOf(Bookmark::class, $bookmark);
    }
}