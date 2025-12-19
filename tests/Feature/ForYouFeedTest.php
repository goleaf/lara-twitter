<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForYouFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_you_ranks_posts_by_engagement_for_guests(): void
    {
        $a = User::factory()->create(['username' => 'a']);
        $b = User::factory()->create(['username' => 'b']);
        $c = User::factory()->create(['username' => 'c']);

        $low = Post::query()->create(['user_id' => $a->id, 'body' => 'Low']);
        $mid = Post::query()->create(['user_id' => $b->id, 'body' => 'Mid']);
        $high = Post::query()->create(['user_id' => $c->id, 'body' => 'High']);

        // Engagement: High (3 reposts), Mid (2 likes), Low (1 reply)
        Post::query()->create(['user_id' => $a->id, 'reply_to_id' => $low->id, 'body' => 'reply']);

        $mid->likes()->create(['user_id' => $a->id]);
        $mid->likes()->create(['user_id' => $c->id]);

        Post::query()->create(['user_id' => $a->id, 'repost_of_id' => $high->id, 'body' => '']);
        Post::query()->create(['user_id' => $b->id, 'repost_of_id' => $high->id, 'body' => '']);
        Post::query()->create(['user_id' => $c->id, 'repost_of_id' => $high->id, 'body' => '']);

        $response = $this->get(route('timeline', ['feed' => 'for-you']));

        $response
            ->assertOk()
            ->assertSeeInOrder(['High', 'Mid', 'Low']);
    }

    public function test_for_you_biases_followed_accounts_when_authenticated(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer']);
        $followed = User::factory()->create(['username' => 'followed']);
        $other = User::factory()->create(['username' => 'other']);

        $viewer->following()->attach($followed->id);

        $fromFollowed = Post::query()->create(['user_id' => $followed->id, 'body' => 'From followed']);
        $fromOther = Post::query()->create(['user_id' => $other->id, 'body' => 'From other']);

        // Give the non-followed post higher raw engagement.
        $fromOther->likes()->create(['user_id' => $viewer->id]);
        $fromOther->likes()->create(['user_id' => $followed->id]);
        $fromOther->likes()->create(['user_id' => $other->id]);

        $response = $this->actingAs($viewer)->get(route('timeline', ['feed' => 'for-you']));

        $response
            ->assertOk()
            ->assertSeeInOrder(['From followed', 'From other']);
    }
}

