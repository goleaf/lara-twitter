<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Block;
use App\Models\Mute;
use App\Notifications\PostMentioned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentionsNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mention_notifies_the_mentioned_user(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $mentioned = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hi @bob',
        ]);

        Notification::assertSentTo(
            $mentioned,
            PostMentioned::class,
            fn (PostMentioned $n) => $n->post->is($post) && $n->mentionedBy->is($author),
        );
    }

    public function test_editing_a_post_to_add_a_new_mention_notifies_only_new_mentions(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hi @bob',
        ]);

        Notification::assertSentTo($bob, PostMentioned::class);
        Notification::assertNotSentTo($carol, PostMentioned::class);

        Notification::fake();

        $post->update(['body' => 'Hi @bob and @carol']);

        Notification::assertNotSentTo($bob, PostMentioned::class);
        Notification::assertSentTo($carol, PostMentioned::class);
    }

    public function test_mentioning_yourself_does_not_notify_you(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);

        Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hi @alice',
        ]);

        Notification::assertNothingSent();
    }

    public function test_mention_does_not_notify_when_mentioned_user_muted_author(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $mentioned = User::factory()->create(['username' => 'bob']);

        Mute::query()->create([
            'muter_id' => $mentioned->id,
            'muted_id' => $author->id,
        ]);

        Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hi @bob',
        ]);

        Notification::assertNotSentTo($mentioned, PostMentioned::class);
    }

    public function test_mention_does_not_notify_when_mentioned_user_blocked_author(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $mentioned = User::factory()->create(['username' => 'bob']);

        Block::query()->create([
            'blocker_id' => $mentioned->id,
            'blocked_id' => $author->id,
        ]);

        Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hi @bob',
        ]);

        Notification::assertNotSentTo($mentioned, PostMentioned::class);
    }
}
