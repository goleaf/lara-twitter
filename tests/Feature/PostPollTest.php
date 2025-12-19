<?php

namespace Tests\Feature;

use App\Livewire\PostCard;
use App\Livewire\PostComposer;
use App\Models\Post;
use App\Models\PostPoll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostPollTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post_with_poll(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PostComposer::class)
            ->set('body', 'Poll time')
            ->set('poll_options', ['Option A', 'Option B'])
            ->set('poll_duration', 1440)
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('post_polls', ['post_id' => $post->id]);
        $this->assertDatabaseHas('post_poll_options', ['option_text' => 'Option A']);
        $this->assertDatabaseHas('post_poll_options', ['option_text' => 'Option B']);
    }

    public function test_poll_requires_at_least_two_options(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PostComposer::class)
            ->set('body', 'Poll time')
            ->set('poll_options', ['Only one'])
            ->set('poll_duration', 1440)
            ->call('save')
            ->assertHasErrors(['poll_options']);
    }

    public function test_user_can_vote_in_poll_and_cannot_vote_twice(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Vote please',
        ]);

        $poll = PostPoll::query()->create([
            'post_id' => $post->id,
            'ends_at' => now()->addDay(),
        ]);

        $a = $poll->options()->create(['option_text' => 'A', 'sort_order' => 0]);
        $b = $poll->options()->create(['option_text' => 'B', 'sort_order' => 1]);

        Livewire::actingAs($user)
            ->test(PostCard::class, ['post' => $post->load(['user', 'images', 'poll.options'])])
            ->call('voteInPoll', $a->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('post_poll_votes', [
            'post_poll_id' => $poll->id,
            'post_poll_option_id' => $a->id,
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(PostCard::class, ['post' => $post->load(['user', 'images', 'poll.options'])])
            ->call('voteInPoll', $b->id)
            ->assertHasNoErrors();

        $this->assertSame(1, $poll->votes()->where('user_id', $user->id)->count());
    }
}

