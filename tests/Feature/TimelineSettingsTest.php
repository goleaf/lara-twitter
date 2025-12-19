<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_following_feed_excludes_replies_by_default(): void
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create(['username' => 'alice']);

        $viewer->following()->attach($author->id);

        $original = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        Post::query()->create([
            'user_id' => $author->id,
            'reply_to_id' => $original->id,
            'body' => 'A reply',
        ]);

        $this->actingAs($viewer)
            ->get(route('timeline', ['feed' => 'following']))
            ->assertOk()
            ->assertDontSee('A reply')
            ->assertSee('Original');
    }

    public function test_following_feed_can_include_replies_when_enabled(): void
    {
        $viewer = User::factory()->create([
            'timeline_settings' => [
                'show_replies' => true,
                'show_retweets' => true,
            ],
        ]);

        $author = User::factory()->create(['username' => 'alice']);
        $viewer->following()->attach($author->id);

        $original = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        Post::query()->create([
            'user_id' => $author->id,
            'reply_to_id' => $original->id,
            'body' => 'A reply',
        ]);

        $this->actingAs($viewer)
            ->get(route('timeline', ['feed' => 'following']))
            ->assertOk()
            ->assertSee('A reply')
            ->assertSee('Replying to');
    }

    public function test_following_feed_can_hide_retweets_when_disabled(): void
    {
        $viewer = User::factory()->create([
            'timeline_settings' => [
                'show_replies' => false,
                'show_retweets' => false,
            ],
        ]);

        $author = User::factory()->create(['username' => 'alice']);
        $viewer->following()->attach($author->id);

        $original = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        Post::query()->create([
            'user_id' => $author->id,
            'repost_of_id' => $original->id,
            'body' => '',
        ]);

        $this->actingAs($viewer)
            ->get(route('timeline', ['feed' => 'following']))
            ->assertOk()
            ->assertSee('Original')
            ->assertDontSee('Retweeted by');
    }
}
