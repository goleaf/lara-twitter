<?php

namespace Tests\Unit\Observers;

use App\Events\NewPostCreated;
use App\Models\Follow;
use App\Models\Hashtag;
use App\Models\Mention;
use App\Models\Post;
use App\Models\PostLinkPreview;
use App\Models\User;
use App\Notifications\FollowedUserPosted;
use App\Notifications\PostMentioned;
use App\Notifications\PostReplied;
use App\Notifications\PostReposted;
use App\Observers\PostObserver;
use App\Services\PostLinkPreviewService;
use App\Services\PostTextParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class PostObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_sets_reply_like_flags(): void
    {
        $observer = app(PostObserver::class);

        $reply = Post::factory()->make([
            'reply_to_id' => 10,
            'body' => '@bob hello',
        ]);
        $observer->saving($reply);
        $this->assertFalse($reply->is_reply_like);

        $mention = Post::factory()->make([
            'reply_to_id' => null,
            'body' => '@bob hello',
        ]);
        $observer->saving($mention);
        $this->assertTrue($mention->is_reply_like);

        $dotMention = Post::factory()->make([
            'reply_to_id' => null,
            'body' => '.@bob hello',
        ]);
        $observer->saving($dotMention);
        $this->assertFalse($dotMention->is_reply_like);
    }

    public function test_saved_syncs_mentions_hashtags_and_deletes_link_preview_when_no_url(): void
    {
        Notification::fake();

        $author = User::factory()->create();
        $mentioned = User::factory()->create(['username' => 'bob']);
        $removed = User::factory()->create(['username' => 'carol']);

        $post = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
            'body' => 'Hello @bob #Tag',
        ]));

        Mention::factory()->create([
            'post_id' => $post->id,
            'mentioned_user_id' => $removed->id,
        ]);

        PostLinkPreview::factory()->create([
            'post_id' => $post->id,
            'url' => 'https://example.com',
        ]);

        $parser = app(PostTextParser::class);
        $linkPreviews = Mockery::mock(PostLinkPreviewService::class);
        $linkPreviews->shouldReceive('extractFirstUrl')
            ->once()
            ->with($post->body)
            ->andReturn(null);

        $observer = new PostObserver($parser, $linkPreviews);
        $observer->saved($post);

        $this->assertTrue(Hashtag::query()->where('tag', 'tag')->exists());
        $this->assertTrue($post->hashtags()->where('tag', 'tag')->exists());
        $this->assertTrue(Mention::query()->where('post_id', $post->id)->where('mentioned_user_id', $mentioned->id)->exists());
        $this->assertFalse(Mention::query()->where('post_id', $post->id)->where('mentioned_user_id', $removed->id)->exists());
        $this->assertFalse(PostLinkPreview::query()->where('post_id', $post->id)->exists());

        Notification::assertSentTo($mentioned, PostMentioned::class);
    }

    public function test_saved_creates_link_preview_when_url_present(): void
    {
        $author = User::factory()->create();

        $post = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
            'body' => 'Check https://example.com',
        ]));

        $parser = app(PostTextParser::class);
        $linkPreviews = Mockery::mock(PostLinkPreviewService::class);
        $linkPreviews->shouldReceive('extractFirstUrl')
            ->once()
            ->with($post->body)
            ->andReturn('https://example.com');
        $linkPreviews->shouldReceive('fetch')->never();

        $observer = new PostObserver($parser, $linkPreviews);
        $observer->saved($post);

        $this->assertSame('https://example.com', PostLinkPreview::query()->where('post_id', $post->id)->value('url'));
    }

    public function test_created_sends_repost_and_reply_notifications(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create();
        $reposter = User::factory()->create();

        $original = Post::withoutEvents(fn () => Post::factory()->for($author)->create());

        $repost = Post::withoutEvents(fn () => Post::factory()->for($reposter)->create([
            'repost_of_id' => $original->id,
            'reply_to_id' => null,
            'body' => '',
        ]));

        $observer = app(PostObserver::class);
        $observer->created($repost);

        Notification::assertSentTo($author, PostReposted::class, function (PostReposted $notification) {
            return $notification->kind === 'retweet';
        });

        $replier = User::factory()->create();
        $reply = Post::withoutEvents(fn () => Post::factory()->for($replier)->create([
            'reply_to_id' => $original->id,
        ]));

        $observer->created($reply);

        Notification::assertSentTo($author, PostReplied::class);
    }

    public function test_created_notifies_followers_for_new_post(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create();
        $follower = User::factory()->create([
            'notification_settings' => ['followed_posts' => true],
        ]);

        Follow::factory()->create([
            'follower_id' => $follower->id,
            'followed_id' => $author->id,
        ]);

        $post = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
            'body' => 'Hello',
        ]));

        $observer = app(PostObserver::class);
        $observer->created($post);

        Notification::assertSentTo($follower, FollowedUserPosted::class);
    }
}
