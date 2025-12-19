<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchOperatorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_supports_from_to_since_until_has_and_min_likes(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $p1 = Post::query()->create(['user_id' => $alice->id, 'body' => 'Laravel http://example.com']);
        $p2 = Post::query()->create(['user_id' => $bob->id, 'body' => 'Hi @alice']);

        $p1->images()->create(['path' => 'posts/1/x.jpg', 'sort_order' => 0]);

        Like::query()->create(['user_id' => $bob->id, 'post_id' => $p1->id]);
        Like::query()->create(['user_id' => $alice->id, 'post_id' => $p1->id]);

        $response = $this->get(route('search', [
            'type' => 'posts',
            'q' => 'from:alice has:links has:images min_likes:2 since:2000-01-01 until:2099-01-01',
        ]));

        $response
            ->assertOk()
            ->assertSee('Laravel')
            ->assertDontSee('Hi @alice');

        $response2 = $this->get(route('search', [
            'type' => 'posts',
            'q' => 'to:alice',
        ]));

        $response2
            ->assertOk()
            ->assertSee('Hi')
            ->assertSee('/@alice');
    }

    public function test_search_supports_exact_phrase_and_exclusion(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello Laravel world']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello Laravel Symfony']);

        $response = $this->get(route('search', [
            'type' => 'posts',
            'q' => '"laravel world" -symfony',
        ]));

        $response
            ->assertOk()
            ->assertSee('Hello Laravel world')
            ->assertDontSee('Hello Laravel Symfony');
    }

    public function test_search_can_sort_top_by_engagement(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);

        $high = Post::query()->create(['user_id' => $alice->id, 'body' => 'News A']);
        $low = Post::query()->create(['user_id' => $bob->id, 'body' => 'News B']);

        Like::query()->create(['user_id' => $bob->id, 'post_id' => $high->id]);
        Like::query()->create(['user_id' => $carol->id, 'post_id' => $high->id]);
        Like::query()->create(['user_id' => $alice->id, 'post_id' => $high->id]);

        Like::query()->create(['user_id' => $alice->id, 'post_id' => $low->id]);

        $response = $this->get(route('search', [
            'type' => 'posts',
            'sort' => 'top',
            'q' => 'news',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['News A', 'News B']);
    }

    public function test_search_supports_filter_verified(): void
    {
        $verified = User::factory()->create(['username' => 'alice', 'is_verified' => true]);
        $unverified = User::factory()->create(['username' => 'bob', 'is_verified' => false]);

        Post::query()->create(['user_id' => $verified->id, 'body' => 'Hello from verified']);
        Post::query()->create(['user_id' => $unverified->id, 'body' => 'Hello from unverified']);

        $response = $this->get(route('search', [
            'type' => 'posts',
            'q' => 'hello filter:verified',
        ]));

        $response
            ->assertOk()
            ->assertSee('Hello from verified')
            ->assertDontSee('Hello from unverified');
    }

    public function test_search_supports_has_videos_and_has_media(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello plain']);

        $video = Post::query()->create([
            'user_id' => $alice->id,
            'body' => 'Hello video',
            'video_path' => 'posts/1/video.mp4',
            'video_mime_type' => 'video/mp4',
        ]);

        $image = Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello image']);
        $image->images()->create(['path' => 'posts/1/x.jpg', 'sort_order' => 0]);

        $responseVideos = $this->get(route('search', [
            'type' => 'posts',
            'q' => 'hello has:videos',
        ]));

        $responseVideos
            ->assertOk()
            ->assertSee('Hello video')
            ->assertDontSee('Hello image')
            ->assertDontSee('Hello plain');

        $responseMedia = $this->get(route('search', [
            'type' => 'posts',
            'q' => 'hello has:media',
        ]));

        $responseMedia
            ->assertOk()
            ->assertSee('Hello video')
            ->assertSee('Hello image')
            ->assertDontSee('Hello plain');

        $this->assertNotNull($video->id);
    }

    public function test_search_supports_or_operator_for_terms(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello Laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello Symfony']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello Rails']);

        $response = $this->get(route('search', [
            'type' => 'posts',
            'q' => 'laravel OR symfony',
        ]));

        $response
            ->assertOk()
            ->assertSee('Hello Laravel')
            ->assertSee('Hello Symfony')
            ->assertDontSee('Hello Rails');
    }
}
