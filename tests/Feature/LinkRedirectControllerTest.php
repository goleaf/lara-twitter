<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkRedirectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_valid_url_query(): void
    {
        $post = Post::factory()->create();

        $this->get(route('links.redirect', $post))
            ->assertStatus(404);

        $this->get(route('links.redirect', ['post' => $post, 'u' => 'ftp://example.com']))
            ->assertStatus(400);
    }

    public function test_redirect_records_analytics_when_enabled(): void
    {
        $author = User::factory()->create(['analytics_enabled' => true]);
        $post = Post::factory()->for($author)->create();

        $analytics = \Mockery::mock(AnalyticsService::class);
        $analytics
            ->shouldReceive('recordUnique')
            ->once()
            ->with('post_link_click', $post->id);
        $this->app->instance(AnalyticsService::class, $analytics);

        $url = 'https://example.com';

        $this->get(route('links.redirect', ['post' => $post, 'u' => $url]))
            ->assertRedirect($url);
    }

    public function test_redirect_skips_analytics_when_disabled(): void
    {
        $author = User::factory()->create(['analytics_enabled' => false, 'is_admin' => false]);
        $post = Post::factory()->for($author)->create();

        $analytics = \Mockery::mock(AnalyticsService::class);
        $analytics->shouldReceive('recordUnique')->never();
        $this->app->instance(AnalyticsService::class, $analytics);

        $url = 'https://example.com';

        $this->get(route('links.redirect', ['post' => $post, 'u' => $url]))
            ->assertRedirect($url);
    }

    public function test_redirect_records_analytics_for_admin(): void
    {
        $author = User::factory()->create(['analytics_enabled' => false, 'is_admin' => true]);
        $post = Post::factory()->for($author)->create();

        $analytics = \Mockery::mock(AnalyticsService::class);
        $analytics
            ->shouldReceive('recordUnique')
            ->once()
            ->with('post_link_click', $post->id);
        $this->app->instance(AnalyticsService::class, $analytics);

        $url = 'https://example.com';

        $this->get(route('links.redirect', ['post' => $post, 'u' => $url]))
            ->assertRedirect($url);
    }
}
