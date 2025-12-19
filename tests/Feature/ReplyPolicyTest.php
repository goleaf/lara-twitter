<?php

namespace Tests\Feature;

use App\Livewire\ReplyComposer;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ReplyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_reply_policy_none_blocks_other_users(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'reply_policy' => Post::REPLY_NONE,
            'body' => 'Original',
        ]);

        Livewire::actingAs($other)
            ->test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Reply')
            ->call('save')
            ->assertStatus(403);
    }

    public function test_reply_policy_following_allows_only_users_the_author_follows(): void
    {
        $author = User::factory()->create();
        $allowed = User::factory()->create();
        $blocked = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'reply_policy' => Post::REPLY_FOLLOWING,
            'body' => 'Original',
        ]);

        $author->following()->attach($allowed->id);

        Livewire::actingAs($allowed)
            ->test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Allowed reply')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($blocked)
            ->test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Blocked reply')
            ->call('save')
            ->assertStatus(403);
    }

    public function test_reply_policy_mentioned_allows_only_mentioned_users(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $mentioned = User::factory()->create(['username' => 'bob']);
        $notMentioned = User::factory()->create(['username' => 'carol']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'reply_policy' => Post::REPLY_MENTIONED,
            'body' => 'Hello @bob',
        ]);

        Livewire::actingAs($mentioned)
            ->test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Hi!')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($notMentioned)
            ->test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Hi!')
            ->call('save')
            ->assertStatus(403);
    }

    public function test_muted_replier_does_not_notify_author(): void
    {
        Notification::fake();

        $author = User::factory()->create();
        $replier = User::factory()->create();

        $author->mutesInitiated()->create(['muted_id' => $replier->id]);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        Livewire::actingAs($replier)
            ->test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Reply')
            ->call('save')
            ->assertHasNoErrors();

        Notification::assertNothingSent();
    }
}
