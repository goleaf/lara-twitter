<?php

namespace Tests\Unit\Services;

use App\Models\Follow;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use App\Services\DiscoverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DiscoverServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_hashtags_returns_expected_map(): void
    {
        $service = app(DiscoverService::class);

        $map = $service->categoryHashtags();

        $this->assertArrayHasKey('news', $map);
        $this->assertArrayHasKey('sports', $map);
        $this->assertContains('news', $map['news']);
    }

    public function test_forget_recommended_users_cache_forgets_unique_limits(): void
    {
        Cache::spy();

        $viewer = User::factory()->create();

        $service = app(DiscoverService::class);

        $service->forgetRecommendedUsersCache($viewer, [5, 0, 5, -2, 8]);

        Cache::shouldHaveReceived('forget')->twice();
    }

    public function test_recommended_users_without_viewer_returns_by_followers(): void
    {
        Cache::flush();

        $top = User::factory()->create(['created_at' => now()->subDays(3)]);
        $mid = User::factory()->create(['created_at' => now()->subDays(2)]);
        $low = User::factory()->create(['created_at' => now()->subDays(1)]);

        User::factory()->count(2)->create()->each(function (User $follower) use ($top): void {
            Follow::factory()->create([
                'follower_id' => $follower->id,
                'followed_id' => $top->id,
            ]);
        });

        Follow::factory()->create([
            'follower_id' => User::factory()->create()->id,
            'followed_id' => $mid->id,
        ]);

        $service = app(DiscoverService::class);

        $users = $service->recommendedUsers(null, 2);

        $this->assertSame([$top->id, $mid->id], $users->pluck('id')->all());
        $this->assertFalse($users->contains('id', $low->id));
    }

    public function test_for_you_posts_excludes_old_posts(): void
    {
        Cache::flush();

        $recent = Post::factory()->create(['created_at' => now()->subDays(2)]);
        $old = Post::factory()->create(['created_at' => now()->subDays(10)]);

        $service = app(DiscoverService::class);

        $posts = $service->forYouPosts(null, 10);

        $this->assertTrue($posts->contains('id', $recent->id));
        $this->assertFalse($posts->contains('id', $old->id));
    }

    public function test_category_posts_filters_by_hashtag(): void
    {
        Cache::flush();

        $tag = Hashtag::factory()->create(['tag' => 'news']);
        $post = Post::factory()->create();
        $post->hashtags()->attach($tag->id);

        $service = app(DiscoverService::class);

        $posts = $service->categoryPosts('news', null, 10);

        $this->assertTrue($posts->contains('id', $post->id));
    }

    public function test_top_posts_for_hashtags_handles_empty_and_known_tags(): void
    {
        Cache::flush();

        $service = app(DiscoverService::class);

        $empty = $service->topPostsForHashtags([], null);
        $this->assertTrue($empty->isEmpty());

        $unknown = $service->topPostsForHashtags(['#missing'], null);
        $this->assertTrue($unknown->isEmpty());

        $tag = Hashtag::factory()->create(['tag' => 'laravel']);
        $post = Post::factory()->create();
        $post->hashtags()->attach($tag->id);

        $result = $service->topPostsForHashtags(['#Laravel'], null, 1, 5);

        $this->assertTrue($result->has('laravel'));
        $this->assertSame($post->id, $result->get('laravel')->first()->id);
    }
}
