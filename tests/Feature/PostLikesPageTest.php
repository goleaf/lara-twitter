<?php

namespace Tests\Feature;

use App\Livewire\PostLikesPage;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostLikesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_likes_page_lists_likers(): void
    {
        $author = User::factory()->create(['username' => 'author']);
        $liker = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello',
        ]);

        Like::query()->create([
            'user_id' => $liker->id,
            'post_id' => $post->id,
        ]);

        Livewire::test(PostLikesPage::class, ['post' => $post])
            ->assertSee('Likes')
            ->assertSee('@bob');
    }
}

