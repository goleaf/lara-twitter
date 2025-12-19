<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_shows_own_posts_and_followed_users_posts(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);

        $alice->following()->attach($bob->id);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Alice post']);
        Post::query()->create(['user_id' => $bob->id, 'body' => 'Bob post']);
        Post::query()->create(['user_id' => $carol->id, 'body' => 'Carol post']);

        $response = $this->actingAs($alice)->get(route('timeline'));

        $response
            ->assertOk()
            ->assertSee('Alice post')
            ->assertSee('Bob post')
            ->assertDontSee('Carol post');
    }

    public function test_hashtag_page_shows_posts_for_tag(): void
    {
        $author = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $author->id, 'body' => 'Hello #laravel']);
        Post::query()->create(['user_id' => $author->id, 'body' => 'Hello #php']);

        $response = $this->get(route('hashtags.show', ['tag' => 'laravel']));

        $response
            ->assertOk()
            ->assertSee('Hello')
            ->assertSee('#laravel')
            ->assertDontSee('#php');
    }

    public function test_mentions_page_requires_auth_and_shows_mentions(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        Post::query()->create(['user_id' => $bob->id, 'body' => 'Hi @alice']);
        Post::query()->create(['user_id' => $bob->id, 'body' => 'Hi @nobody']);

        $this->get(route('mentions'))->assertRedirect();

        $response = $this->actingAs($alice)->get(route('mentions'));

        $response
            ->assertOk()
            ->assertSee('Hi')
            ->assertSee('@alice');
    }
}

