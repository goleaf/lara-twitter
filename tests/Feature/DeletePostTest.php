<?php

namespace Tests\Feature;

use App\Livewire\PostCard;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DeletePostTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_delete_their_post_and_images_are_removed(): void
    {
        Storage::fake('public');

        $author = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello',
        ]);

        $path = "posts/{$post->id}/one.jpg";
        Storage::disk('public')->put($path, 'x');
        $post->images()->create(['path' => $path, 'sort_order' => 0]);

        Livewire::actingAs($author)
            ->test(PostCard::class, ['post' => $post])
            ->call('deletePost');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_images', ['post_id' => $post->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_other_user_cannot_delete_post(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello',
        ]);

        Livewire::actingAs($other)
            ->test(PostCard::class, ['post' => $post])
            ->call('deletePost')
            ->assertStatus(403);
    }

    public function test_admin_can_delete_any_post(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello',
        ]);

        Livewire::actingAs($admin)
            ->test(PostCard::class, ['post' => $post])
            ->call('deletePost');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_deleting_original_post_removes_retweets(): void
    {
        $author = User::factory()->create();
        $retweeter = User::factory()->create();

        $original = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        $retweet = Post::query()->create([
            'user_id' => $retweeter->id,
            'repost_of_id' => $original->id,
            'body' => '',
        ]);

        Livewire::actingAs($author)
            ->test(PostCard::class, ['post' => $original])
            ->call('deletePost');

        $this->assertDatabaseMissing('posts', ['id' => $original->id]);
        $this->assertDatabaseMissing('posts', ['id' => $retweet->id]);
    }
}

