<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreTest extends TestCase
{
    use RefreshDatabase;

    public function test_explore_page_is_accessible_to_guests(): void
    {
        $this->get(route('explore'))->assertOk()->assertSee('Explore');
    }

    public function test_explore_category_shows_matching_posts(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'Breaking #news']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Matchday #sports']);

        $response = $this->get(route('explore', ['tab' => 'news']));
        $response->assertOk()->assertSee('Breaking')->assertDontSee('Matchday');
    }

    public function test_explore_for_you_tab_shows_popular_posts_first(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer']);
        $author = User::factory()->create(['username' => 'author']);

        $fresh = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Fresh post',
            'created_at' => now(),
        ]);

        $popular = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Popular post',
            'created_at' => now()->subHour(),
        ]);

        Like::query()->create(['user_id' => $viewer->id, 'post_id' => $popular->id]);
        Like::query()->create(['user_id' => User::factory()->create()->id, 'post_id' => $popular->id]);

        $response = $this->actingAs($viewer)->get(route('explore', ['tab' => 'for-you']));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Popular post', 'Fresh post']);
    }

    public function test_explore_technology_category_shows_matching_posts(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'New #tech']);
        Post::query()->create(['user_id' => $alice->id, 'body' => 'Matchday #sports']);

        $response = $this->get(route('explore', ['tab' => 'technology']));
        $response->assertOk()->assertSee('New')->assertDontSee('Matchday');
    }

    public function test_recommended_accounts_suggests_mutual_follow_graph(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer']);
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        // viewer follows alice; alice follows bob => bob recommended for viewer.
        $viewer->following()->attach($alice->id);
        $alice->following()->attach($bob->id);

        $response = $this->actingAs($viewer)->get(route('explore'));

        $response->assertOk()->assertSee('Recommended accounts')->assertSee('bob');
    }
}
