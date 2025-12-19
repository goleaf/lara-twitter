<?php

namespace Tests\Feature;

use App\Livewire\ReplyComposer;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReplyComposerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_reply_with_images(): void
    {
        Storage::fake('public');
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $replier = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        Livewire::actingAs($replier)
            ->test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Replying to @alice #Nice')
            ->set('images', [UploadedFile::fake()->image('reply.jpg')])
            ->call('save')
            ->assertHasNoErrors();

        $reply = Post::query()->where('reply_to_id', $post->id)->firstOrFail();

        $this->assertDatabaseHas('mentions', [
            'post_id' => $reply->id,
            'mentioned_user_id' => $author->id,
        ]);
        $this->assertDatabaseHas('hashtags', ['tag' => 'nice']);

        $this->assertCount(1, $reply->images);
        Storage::disk('public')->assertExists($reply->images[0]->path);

        Notification::assertSentTo(
            $author,
            PostReplied::class,
            fn (PostReplied $notification) => $notification->replyPost->is($reply)
                && $notification->originalPost->is($post)
                && $notification->replier->is($replier),
        );
    }

    public function test_reply_does_not_notify_when_replying_to_self(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        Livewire::actingAs($author)
            ->test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Self reply')
            ->call('save')
            ->assertHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_guest_cannot_reply(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        Livewire::test(ReplyComposer::class, ['post' => $post])
            ->set('body', 'Reply')
            ->call('save')
            ->assertStatus(403);
    }
}
