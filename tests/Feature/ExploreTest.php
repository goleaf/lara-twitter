<?php

namespace Tests\Feature;

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

