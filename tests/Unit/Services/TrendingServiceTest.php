<?php

namespace Tests\Unit\Services;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use App\Services\TrendingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
}
