<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Hashtag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostThreadTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_thread_page_renders_post_and_replies(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $replier = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world #laravel',
        ]);

        Post::query()->create([
            'user_id' => $replier->id,
            'reply_to_id' => $post->id,
            'body' => 'Nice post @alice',
        ]);

        $response = $this->get(route('posts.show', $post));

        $response
            ->assertOk()
            ->assertSee('Hello world')
            ->assertSee('Nice post');
    }

    public function test_mentions_and_hashtags_are_extracted_on_save(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $mentioned = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hey @bob check #Laravel #2025',
        ]);

        $this->assertDatabaseHas('hashtags', ['tag' => 'laravel']);
        $this->assertDatabaseHas('hashtags', ['tag' => '2025']);
        $this->assertDatabaseHas('hashtag_post', ['post_id' => $post->id]);
        $this->assertDatabaseHas('hashtag_post', [
            'post_id' => $post->id,
            'hashtag_id' => Hashtag::query()->where('tag', '2025')->value('id'),
        ]);
        $this->assertDatabaseHas('mentions', [
            'post_id' => $post->id,
            'mentioned_user_id' => $mentioned->id,
        ]);
    }
}
