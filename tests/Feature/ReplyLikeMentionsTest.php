<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplyLikeMentionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_starting_with_mention_is_treated_as_reply_like_and_hidden_from_posts_tab(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $target = User::factory()->create(['username' => 'bob']);

        Post::query()->create(['user_id' => $author->id, 'body' => '@bob hi']);

        $this->get(route('profile.show', ['user' => $author]))
            ->assertOk()
            ->assertDontSee('hi');

        $this->get(route('profile.replies', ['user' => $author]))
            ->assertOk()
            ->assertSee('hi');
    }

    public function test_dot_mention_is_not_reply_like(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        User::factory()->create(['username' => 'bob']);

        Post::query()->create(['user_id' => $author->id, 'body' => '.@bob hi']);

        $this->get(route('profile.show', ['user' => $author]))
            ->assertOk()
            ->assertSee('hi');
    }

    public function test_timeline_following_excludes_reply_like_by_default_and_can_include_when_enabled(): void
    {
        $viewer = User::factory()->create(['timeline_settings' => ['show_replies' => false, 'show_retweets' => true]]);
        $author = User::factory()->create(['username' => 'alice']);
        User::factory()->create(['username' => 'bob']);

        $viewer->following()->attach($author->id);

        Post::query()->create(['user_id' => $author->id, 'body' => '@bob hello']);

        $this->actingAs($viewer)
            ->get(route('timeline', ['feed' => 'following']))
            ->assertOk()
            ->assertDontSee('hello');

        $viewer->update(['timeline_settings' => ['show_replies' => true, 'show_retweets' => true]]);

        $this->actingAs($viewer)
            ->get(route('timeline', ['feed' => 'following']))
            ->assertOk()
            ->assertSee('hello')
            ->assertSee('Replying to');
    }
}

