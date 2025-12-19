<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_trending_hashtags_shows_most_used_tags(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello #laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Again #laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Once #php']);

        $response = $this->get(route('trending'));

        $response
            ->assertOk()
            ->assertSee('Trending')
            ->assertSee('#laravel')
            ->assertSee('#php');
    }

    public function test_trending_hashtags_can_be_filtered_by_location(): void
    {
        $berliner = User::factory()->create(['username' => 'berliner', 'location' => 'Berlin']);
        $parisian = User::factory()->create(['username' => 'parisian', 'location' => 'Paris']);

        Post::query()->create(['user_id' => $berliner->id, 'body' => 'Hello #berlin']);
        Post::query()->create(['user_id' => $parisian->id, 'body' => 'Hello #paris']);

        $response = $this->get(route('trending', ['loc' => 'berlin']));

        $response
            ->assertOk()
            ->assertSee('#berlin')
            ->assertDontSee('#paris');
    }

    public function test_trending_keywords_shows_keywords_tab(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Laravel framework is great']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Laravel queue workers']);

        $response = $this->get(route('trending', ['tab' => 'keywords']));

        $response
            ->assertOk()
            ->assertSee('Trending keywords')
            ->assertSee('laravel');
    }

    public function test_trending_hashtag_interest_bias_prioritizes_interest_tags(): void
    {
        $alice = User::factory()->create([
            'username' => 'alice',
            'interest_hashtags' => ['php'],
        ]);

        Post::query()->create(['user_id' => $alice->id, 'body' => '#laravel #laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => '#laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => '#php']);

        $response = $this->actingAs($alice)->get(route('trending'));

        $response
            ->assertOk()
            ->assertSeeInOrder(['#php', '#laravel']);
    }
}
