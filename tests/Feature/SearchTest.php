<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_posts_by_keyword_is_case_insensitive(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello Laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello Symfony']);

        $response = $this->get(route('search', ['q' => 'laravel', 'type' => 'posts']));

        $response
            ->assertOk()
            ->assertSee('Hello Laravel')
            ->assertDontSee('Hello Symfony');
    }

    public function test_search_posts_by_hashtag(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello #Laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello #PHP']);

        $response = $this->get(route('search', ['q' => '#laravel', 'type' => 'posts']));

        $response
            ->assertOk()
            ->assertSee('#Laravel')
            ->assertDontSee('#PHP');
    }

    public function test_search_posts_can_filter_by_username_and_date_range(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $old = Post::query()->create(['user_id' => $alice->id, 'body' => 'Old post']);
        $new = Post::query()->create(['user_id' => $alice->id, 'body' => 'New post']);
        Post::query()->create(['user_id' => $bob->id, 'body' => 'Bob post']);

        DB::table('posts')->where('id', $old->id)->update([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        $response = $this->get(route('search', [
            'q' => 'post',
            'type' => 'posts',
            'user' => '@alice',
            'from' => now()->subDays(2)->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response
            ->assertOk()
            ->assertSee('New post')
            ->assertDontSee('Old post')
            ->assertDontSee('Bob post');
    }

    public function test_search_users_by_username(): void
    {
        User::factory()->create(['username' => 'alice', 'name' => 'Alice A']);
        User::factory()->create(['username' => 'bob', 'name' => 'Bob B']);

        $response = $this->get(route('search', ['q' => 'ali', 'type' => 'users']));

        $response
            ->assertOk()
            ->assertSee('@alice')
            ->assertDontSee('@bob');
    }

    public function test_search_users_by_at_username(): void
    {
        User::factory()->create(['username' => 'alice', 'name' => 'Alice A']);
        User::factory()->create(['username' => 'bob', 'name' => 'Bob B']);

        $response = $this->get(route('search', ['q' => '@ali', 'type' => 'users']));

        $response
            ->assertOk()
            ->assertSee('@alice')
            ->assertDontSee('@bob');
    }

    public function test_search_page_shows_trending_hashtags_when_empty_query(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello #laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello #laravel again']);

        $response = $this->get(route('search'));

        $response
            ->assertOk()
            ->assertSee('Trending hashtags')
            ->assertSee('#laravel');
    }

    public function test_search_media_type_shows_only_posts_with_media(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        $video = Post::query()->create([
            'user_id' => $alice->id,
            'body' => 'Hello from video',
            'video_path' => 'posts/1/video.mp4',
            'video_mime_type' => 'video/mp4',
        ]);

        Post::query()->create([
            'user_id' => $alice->id,
            'body' => 'Hello from text',
        ]);

        $response = $this->get(route('search', ['q' => 'hello', 'type' => 'media']));

        $response
            ->assertOk()
            ->assertSee('Hello from video')
            ->assertDontSee('Hello from text');

        $this->assertNotNull($video->id);
    }
}
