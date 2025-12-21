<?php

namespace Tests\Unit\Models;

use App\Models\PostImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_postImage_can_be_created(): void
    {
        $postImage = PostImage::factory()->create();

        $this->assertInstanceOf(PostImage::class, $postImage);
        $this->assertDatabaseHas('post_images', [
            'id' => $postImage->id,
        ]);
    }

    public function test_postImage_has_factory(): void
    {
        $postImage = PostImage::factory()->make();

        $this->assertInstanceOf(PostImage::class, $postImage);
    }
}