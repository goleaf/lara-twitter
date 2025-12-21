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

    public function test_recommended_users_with_viewer_returns_mutuals_and_stops_at_limit(): void
    {
        Cache::flush();

        $viewer = User::factory()->create();
        $followedA = User::factory()->create();
        $followedB = User::factory()->create();

        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $followedA->id,
        ]);

        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $followedB->id,
        ]);

        $candidate = User::factory()->create();
        Follow::factory()->create([
            'follower_id' => $followedA->id,
            'followed_id' => $candidate->id,
        ]);

        $service = app(DiscoverService::class);

        $users = $service->recommendedUsers($viewer, 1);

        $this->assertSame([$candidate->id], $users->pluck('id')->all());
    }

    public function test_recommended_users_with_interest_tags_fills_remaining(): void
    {
        Cache::flush();

        $viewer = User::factory()->create(['interest_hashtags' => ['laravel']]);
        $interestUser = User::factory()->create();

        $tag = Hashtag::factory()->create(['tag' => 'laravel']);
        $post = Post::factory()->create([
            'user_id' => $interestUser->id,
            'created_at' => now()->subDays(5),
        ]);
        $post->hashtags()->attach($tag->id);

        $service = app(DiscoverService::class);

        $users = $service->recommendedUsers($viewer, 1);

        $this->assertTrue($users->contains('id', $interestUser->id));
    }

    public function test_recommended_users_falls_back_when_no_mutuals_or_interests(): void
    {
        Cache::flush();

        $viewer = User::factory()->create();
        $popular = User::factory()->create();
        $other = User::factory()->create();

        Follow::factory()->create([
            'follower_id' => User::factory()->create()->id,
            'followed_id' => $popular->id,
        ]);

        $service = app(DiscoverService::class);

        $users = $service->recommendedUsers($viewer, 1);

        $this->assertSame($popular->id, $users->first()->id);
        $this->assertFalse($users->contains('id', $viewer->id));
        $this->assertFalse($users->contains('id', $other->id));
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

    public function test_for_you_posts_applies_viewer_exclusions_and_terms(): void
    {
        Cache::flush();

        $viewer = User::factory()->create(['interest_hashtags' => ['laravel']]);
        $followed = User::factory()->create();
        $blocked = User::factory()->create();
        $author = User::factory()->create();

        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $followed->id,
        ]);

        $viewer->blocksInitiated()->create([
            'blocked_id' => $blocked->id,
        ]);

        Hashtag::factory()->create(['tag' => 'laravel']);

        Post::factory()->create([
            'user_id' => $blocked->id,
            'body' => 'clean',
            'created_at' => now()->subHour(),
        ]);

        $allowed = Post::factory()->create([
            'user_id' => $followed->id,
            'body' => 'hello from followed',
            'created_at' => now()->subHour(),
        ]);

        Post::factory()->create([
            'user_id' => $author->id,
            'body' => '#spoiler content',
            'created_at' => now()->subHour(),
        ]);

        Post::factory()->create([
            'user_id' => $author->id,
            'body' => 'clean post',
            'created_at' => now()->subHour(),
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

        $service = app(DiscoverService::class);

        $posts = $service->forYouPosts($viewer, 10);

        $this->assertTrue($posts->contains('id', $allowed->id));
        $this->assertFalse($posts->contains('user_id', $blocked->id));
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

    public function test_category_posts_orders_followed_users_first(): void
    {
        Cache::flush();

        $viewer = User::factory()->create();
        $followed = User::factory()->create();
        $other = User::factory()->create();

        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $followed->id,
        ]);

        $tag = Hashtag::factory()->create(['tag' => 'news']);

        $followedPost = Post::factory()->create([
            'user_id' => $followed->id,
            'created_at' => now()->subHour(),
        ]);
        $followedPost->hashtags()->attach($tag->id);

        $otherPost = Post::factory()->create([
            'user_id' => $other->id,
            'created_at' => now()->subHour(),
        ]);
        $otherPost->hashtags()->attach($tag->id);

        $service = app(DiscoverService::class);

        $posts = $service->categoryPosts('news', $viewer, 10);

        $this->assertTrue($posts->contains('id', $followedPost->id));
        $this->assertTrue($posts->contains('id', $otherPost->id));
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

    public function test_top_posts_for_hashtags_with_viewer_applies_filters(): void
    {
        Cache::flush();

        $viewer = User::factory()->create();
        $author = User::factory()->create();

        $tag = Hashtag::factory()->create(['tag' => 'laravel']);
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'created_at' => now()->subHour(),
        ]);
        $post->hashtags()->attach($tag->id);

        $viewer->mutedTerms()->create([
            'term' => 'ignore',
            'whole_word' => false,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'mute_notifications' => false,
            'expires_at' => null,
        ]);

        $service = app(DiscoverService::class);

        $result = $service->topPostsForHashtags(['laravel'], $viewer, 1, 5);

        $this->assertSame($post->id, $result->get('laravel')->first()->id);
    }
}
