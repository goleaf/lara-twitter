<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HashtagTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_hashtag_page_supports_latest_and_top_tabs(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $low = Post::query()->create(['user_id' => $alice->id, 'body' => 'Low #laravel']);
        $high = Post::query()->create(['user_id' => $bob->id, 'body' => 'High #laravel']);

        Like::query()->create(['user_id' => $alice->id, 'post_id' => $high->id]);
        Like::query()->create(['user_id' => $bob->id, 'post_id' => $high->id]);

        $this->get(route('hashtags.show', ['tag' => 'laravel', 'sort' => 'top']))
            ->assertOk()
            ->assertSeeInOrder(['High', 'Low']);
    }

    public function test_invalid_hashtag_returns_404(): void
    {
        $this->get(route('hashtags.show', ['tag' => 'bad-tag!']))
            ->assertNotFound();
    }
}
