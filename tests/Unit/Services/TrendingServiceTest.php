<?php

namespace Tests\Unit\Services;

use App\Models\Hashtag;
use App\Models\Block;
use App\Models\Follow;
use App\Models\MutedTerm;
use App\Models\Post;
use App\Models\User;
use App\Services\TrendingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TrendingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_trending_hashtags_returns_ranked_tags(): void
    {
        Cache::flush();

        $tag = Hashtag::factory()->create(['tag' => 'laravel']);
        $post = Post::factory()->create([
            'body' => 'Plain post',
            'created_at' => now()->subHours(2),
        ]);
        $post->hashtags()->attach($tag->id);

        $service = app(TrendingService::class);

        $results = $service->trendingHashtags(null, 5);

        $this->assertCount(1, $results);
        $this->assertSame($tag->id, $results->first()->id);
        $this->assertNotNull($results->first()->trend_score);
    }

    public function test_trending_topics_returns_categories(): void
    {
        Cache::flush();

        $tag = Hashtag::factory()->create(['tag' => 'news']);
        $post = Post::factory()->create(['created_at' => now()->subHours(2)]);
        $post->hashtags()->attach($tag->id);

        $service = app(TrendingService::class);

        $results = $service->trendingTopics(null, 5);

        $this->assertTrue($results->contains('category', 'news'));
    }

    public function test_trending_conversations_returns_posts(): void
    {
        Cache::flush();

        $post = Post::factory()->create(['created_at' => now()->subHours(2)]);

        $service = app(TrendingService::class);

        $results = $service->trendingConversations(null, 10);

        $this->assertTrue($results->contains('id', $post->id));
    }

    public function test_trending_keywords_returns_keywords(): void
    {
        Cache::flush();

        $user = User::factory()->create();

        Post::factory()->create([
            'user_id' => $user->id,
            'body' => 'Laravel testing improves quality',
            'created_at' => now()->subMinutes(30),
        ]);

        $service = app(TrendingService::class);

        $results = $service->trendingKeywords(null, 10);

        $keywords = $results->pluck('keyword')->all();

        $this->assertContains('laravel', $keywords);
        $this->assertContains('testing', $keywords);
    }

    public function test_trending_methods_return_empty_when_tables_missing(): void
    {
        Schema::partialMock()->shouldReceive('hasTable')->andReturn(false);

        $service = app(TrendingService::class);

        $this->assertTrue($service->trendingHashtags(null)->isEmpty());
        $this->assertTrue($service->trendingTopics(null)->isEmpty());
        $this->assertTrue($service->trendingConversations(null)->isEmpty());
        $this->assertTrue($service->trendingKeywords(null)->isEmpty());
    }

    public function test_trending_hashtags_respects_location_interests_and_muted_terms(): void
    {
        Cache::flush();

        $viewer = User::factory()->create(['interest_hashtags' => ['laravel']]);
        $allowedAuthor = User::factory()->create(['location' => 'Berlin']);
        $mutedAuthor = User::factory()->create(['location' => 'Berlin']);
        $blockedAuthor = User::factory()->create(['location' => 'Berlin']);

        Block::factory()->create([
            'blocker_id' => $viewer->id,
            'blocked_id' => $blockedAuthor->id,
        ]);

        MutedTerm::factory()->create([
            'user_id' => $viewer->id,
            'term' => 'php',
            'whole_word' => true,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'expires_at' => null,
        ]);

        $laravel = Hashtag::factory()->create(['tag' => 'laravel']);
        $php = Hashtag::factory()->create(['tag' => 'php']);
        $blocked = Hashtag::factory()->create(['tag' => 'blocked']);

        $post = Post::factory()->create([
            'user_id' => $allowedAuthor->id,
            'created_at' => now()->subHours(2),
        ]);
        $post->hashtags()->attach($laravel->id);

        $mutedPost = Post::factory()->create([
            'user_id' => $mutedAuthor->id,
            'created_at' => now()->subHours(2),
        ]);
        $mutedPost->hashtags()->attach($php->id);

        $blockedPost = Post::factory()->create([
            'user_id' => $blockedAuthor->id,
            'created_at' => now()->subHours(2),
        ]);
        $blockedPost->hashtags()->attach($blocked->id);

        $service = app(TrendingService::class);

        $results = $service->trendingHashtags($viewer, 10, 'Berlin');
        $tags = $results->pluck('tag')->all();

        $this->assertContains('laravel', $tags);
        $this->assertNotContains('php', $tags);
        $this->assertNotContains('blocked', $tags);
    }

    public function test_trending_topics_with_location_and_interests(): void
    {
        Cache::flush();

        $viewer = User::factory()->create(['interest_hashtags' => ['news']]);
        $author = User::factory()->create(['location' => 'Berlin']);

        $news = Hashtag::factory()->create(['tag' => 'news']);
        $sports = Hashtag::factory()->create(['tag' => 'sports']);

        $newsPost = Post::factory()->create([
            'user_id' => $author->id,
            'created_at' => now()->subHours(2),
        ]);
        $newsPost->hashtags()->attach($news->id);

        $sportsPost = Post::factory()->create([
            'user_id' => $author->id,
            'created_at' => now()->subHours(2),
        ]);
        $sportsPost->hashtags()->attach($sports->id);

        $service = app(TrendingService::class);

        $results = $service->trendingTopics($viewer, 5, 'Berlin');

        $this->assertTrue($results->contains('category', 'news'));
    }

    public function test_trending_conversations_with_viewer_filters(): void
    {
        Cache::flush();

        $viewer = User::factory()->create();
        $followed = User::factory()->create(['location' => 'Berlin']);
        $other = User::factory()->create(['location' => 'Berlin']);
        $blocked = User::factory()->create(['location' => 'Berlin']);

        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $followed->id,
        ]);

        Block::factory()->create([
            'blocker_id' => $viewer->id,
            'blocked_id' => $blocked->id,
        ]);

        $viewer->mutedTerms()->create([
            'term' => '',
            'whole_word' => true,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'mute_notifications' => false,
            'expires_at' => null,
        ]);

        $viewer->mutedTerms()->create([
            'term' => 'hello',
            'whole_word' => true,
            'only_non_followed' => true,
            'mute_timeline' => true,
            'mute_notifications' => false,
            'expires_at' => null,
        ]);

        $viewer->mutedTerms()->create([
            'term' => '#spoiler',
            'whole_word' => false,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'mute_notifications' => false,
            'expires_at' => null,
        ]);

        $allowedPost = Post::factory()->create([
            'user_id' => $followed->id,
            'body' => 'hello from followed',
            'created_at' => now()->subHour(),
        ]);

        Post::factory()->create([
            'user_id' => $other->id,
            'body' => '#spoiler',
            'created_at' => now()->subHour(),
        ]);

        Post::factory()->create([
            'user_id' => $blocked->id,
            'body' => 'clean',
            'created_at' => now()->subHour(),
        ]);

        $service = app(TrendingService::class);

        $results = $service->trendingConversations($viewer, 10, 'Berlin');

        $this->assertTrue($results->contains('id', $allowedPost->id));
        $this->assertFalse($results->contains('user_id', $blocked->id));
    }

    public function test_trending_keywords_handles_urls_mentions_stopwords_and_muted_terms(): void
    {
        Cache::flush();

        $viewer = User::factory()->create(['location' => 'Berlin']);

        MutedTerm::factory()->create([
            'user_id' => $viewer->id,
            'term' => 'laravel',
            'whole_word' => true,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'expires_at' => null,
        ]);

        Post::factory()->create([
            'user_id' => $viewer->id,
            'body' => 'Laravel testing improves quality',
            'created_at' => now()->subMinutes(30),
        ]);

        Post::factory()->create([
            'user_id' => $viewer->id,
            'body' => 'Visit https://example.com for updates',
            'created_at' => now()->subMinutes(40),
        ]);

        Post::factory()->create([
            'user_id' => $viewer->id,
            'body' => 'Talk with @user about #Laravel',
            'created_at' => now()->subMinutes(50),
        ]);

        Post::factory()->create([
            'user_id' => $viewer->id,
            'body' => 'and the for with',
            'created_at' => now()->subMinutes(20),
        ]);

        Post::factory()->create([
            'user_id' => $viewer->id,
            'body' => '123',
            'created_at' => now()->subMinutes(10),
        ]);

        $service = app(TrendingService::class);

        $results = $service->trendingKeywords($viewer, 10, 'Berlin');
        $keywords = $results->pluck('keyword')->all();

        $this->assertNotContains('laravel', $keywords);
        $this->assertContains('testing', $keywords);
        $this->assertNotContains('the', $keywords);
    }
}
