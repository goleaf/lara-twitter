<?php

namespace Tests\Feature;

use App\Livewire\PostCard;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_and_unlike_a_post(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        Livewire::actingAs($liker)
            ->test(PostCard::class, ['post' => $post])
            ->call('toggleLike');

        $this->assertDatabaseHas('likes', [
            'user_id' => $liker->id,
            'post_id' => $post->id,
        ]);

        Livewire::actingAs($liker)
            ->test(PostCard::class, ['post' => $post])
            ->call('toggleLike');

        $this->assertDatabaseMissing('likes', [
            'user_id' => $liker->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_guest_cannot_like(): void
    {
        $author = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        Livewire::test(PostCard::class, ['post' => $post])
            ->call('toggleLike')
            ->assertStatus(403);
    }
}
