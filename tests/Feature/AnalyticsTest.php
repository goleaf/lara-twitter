<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_page_requires_auth_and_opt_in(): void
    {
        $user = User::factory()->create(['analytics_enabled' => false]);

        $this->get(route('analytics'))->assertRedirect('/login');

        $this->actingAs($user)->get(route('analytics'))->assertForbidden();
    }

    public function test_profile_and_post_views_are_recorded_as_unique_daily_events(): void
    {
        $author = User::factory()->create(['username' => 'alice', 'analytics_enabled' => true]);
        $viewer = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        $this->actingAs($viewer)->get(route('profile.show', ['user' => $author]))->assertOk();
        $this->actingAs($viewer)->get(route('profile.show', ['user' => $author]))->assertOk();

        $this->assertSame(1, (int) DB::table('analytics_uniques')
            ->where('type', 'profile_view')
            ->where('entity_id', $author->id)
            ->count());

        $this->actingAs($viewer)->get(route('posts.show', $post))->assertOk();
        $this->actingAs($viewer)->get(route('posts.show', $post))->assertOk();

        $this->assertSame(1, (int) DB::table('analytics_uniques')
            ->where('type', 'post_view')
            ->where('entity_id', $post->id)
            ->count());
    }

    public function test_opted_in_user_can_view_analytics_summary(): void
    {
        $author = User::factory()->create(['username' => 'alice', 'analytics_enabled' => true]);
        $viewer = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        $this->actingAs($viewer)->get(route('profile.show', ['user' => $author]))->assertOk();
        $this->actingAs($viewer)->get(route('posts.show', $post))->assertOk();

        $response = $this->actingAs($author)->get(route('analytics'));

        $response
            ->assertOk()
            ->assertSee('Analytics')
            ->assertSee('Impressions')
            ->assertSee('Profile visits');
    }

    public function test_link_clicks_are_recorded_as_unique_daily_events(): void
    {
        $author = User::factory()->create(['username' => 'alice', 'analytics_enabled' => true]);
        $viewer = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'https://example.com']);

        $this->actingAs($viewer)->get(route('links.redirect', ['post' => $post->id, 'u' => 'https://example.com']))->assertRedirect('https://example.com');
        $this->actingAs($viewer)->get(route('links.redirect', ['post' => $post->id, 'u' => 'https://example.com']))->assertRedirect('https://example.com');

        $this->assertSame(1, (int) DB::table('analytics_uniques')
            ->where('type', 'post_link_click')
            ->where('entity_id', $post->id)
            ->count());
    }

    public function test_profile_clicks_from_posts_are_recorded_as_unique_daily_events(): void
    {
        $author = User::factory()->create(['username' => 'alice', 'analytics_enabled' => true]);
        $viewer = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        $this->actingAs($viewer)->get(route('profile.show', ['user' => $author, 'from_post' => $post->id]))->assertOk();
        $this->actingAs($viewer)->get(route('profile.show', ['user' => $author, 'from_post' => $post->id]))->assertOk();

        $this->assertSame(1, (int) DB::table('analytics_uniques')
            ->where('type', 'post_profile_click')
            ->where('entity_id', $post->id)
            ->count());
    }

    public function test_media_views_are_recorded_for_posts_with_media_as_unique_daily_events(): void
    {
        $author = User::factory()->create(['username' => 'alice', 'analytics_enabled' => true]);
        $viewer = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);
        PostImage::query()->create(['post_id' => $post->id, 'path' => 'seed-images/test.jpg', 'sort_order' => 0]);

        $this->actingAs($viewer)->get(route('posts.show', $post))->assertOk();
        $this->actingAs($viewer)->get(route('posts.show', $post))->assertOk();

        $this->assertSame(1, (int) DB::table('analytics_uniques')
            ->where('type', 'post_media_view')
            ->where('entity_id', $post->id)
            ->count());
    }
}
