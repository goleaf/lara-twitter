<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\MutedTerm;
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

    public function test_trending_conversations_tab_shows_posts(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        $post = Post::query()->create(['user_id' => $alice->id, 'body' => 'Hot conversation']);

        // Replies should not be considered a "conversation" trend.
        Post::query()->create(['user_id' => $alice->id, 'body' => 'A reply', 'reply_to_id' => $post->id]);

        $response = $this->get(route('trending', ['tab' => 'conversations']));

        $response
            ->assertOk()
            ->assertSee('Hot conversation')
            ->assertDontSee('A reply');
    }

    public function test_trending_keywords_can_be_filtered_by_location(): void
    {
        $berliner = User::factory()->create(['username' => 'berliner', 'location' => 'Berlin']);
        $parisian = User::factory()->create(['username' => 'parisian', 'location' => 'Paris']);

        Post::query()->create(['user_id' => $berliner->id, 'body' => 'Berlin party tonight']);
        Post::query()->create(['user_id' => $parisian->id, 'body' => 'Paris croissant morning']);

        $response = $this->get(route('trending', ['tab' => 'keywords', 'loc' => 'berlin']));

        $response
            ->assertOk()
            ->assertSee('berlin')
            ->assertDontSee('paris');
    }

    public function test_trending_conversations_can_be_filtered_by_location(): void
    {
        $berliner = User::factory()->create(['username' => 'berliner', 'location' => 'Berlin']);
        $parisian = User::factory()->create(['username' => 'parisian', 'location' => 'Paris']);

        Post::query()->create(['user_id' => $berliner->id, 'body' => 'Berlin conversation']);
        Post::query()->create(['user_id' => $parisian->id, 'body' => 'Paris conversation']);

        $response = $this->get(route('trending', ['tab' => 'conversations', 'loc' => 'berlin']));

        $response
            ->assertOk()
            ->assertSee('Berlin conversation')
            ->assertDontSee('Paris conversation');
    }

    public function test_muted_terms_hide_trends_for_authenticated_user(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        MutedTerm::query()->create([
            'user_id' => $alice->id,
            'term' => 'laravel',
            'whole_word' => false,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'mute_notifications' => false,
            'expires_at' => null,
        ]);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello #laravel']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello #php']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Laravel framework']);

        $response = $this->actingAs($alice)->get(route('trending'));

        $response
            ->assertOk()
            ->assertDontSee('#laravel')
            ->assertSee('#php');

        $response = $this->actingAs($alice)->get(route('trending', ['tab' => 'keywords']));

        $response
            ->assertOk()
            ->assertDontSee('laravel');
    }
}
