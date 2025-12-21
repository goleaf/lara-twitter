<?php

namespace Tests\Unit\Models;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_can_be_created(): void
    {
        $post = Post::factory()->create();

        $this->assertInstanceOf(Post::class, $post);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_post_has_factory(): void
    {
        $post = Post::factory()->make();

        $this->assertInstanceOf(Post::class, $post);
    }
}