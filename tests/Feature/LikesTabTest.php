<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikesTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_likes_tab_shows_liked_posts(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $liker = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world #laravel',
        ]);

        $post->likes()->create(['user_id' => $liker->id]);

        $response = $this->get(route('profile.likes', ['user' => $liker]));

        $response
            ->assertOk()
            ->assertSee('Likes')
            ->assertSee('Hello world');
    }

    public function test_likes_tab_does_not_include_replies(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $liker = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        $reply = Post::query()->create([
            'user_id' => $author->id,
            'reply_to_id' => $post->id,
            'body' => 'A reply',
        ]);

        $reply->likes()->create(['user_id' => $liker->id]);

        $response = $this->get(route('profile.likes', ['user' => $liker]));

        $response
            ->assertOk()
            ->assertDontSee('A reply');
    }
}

