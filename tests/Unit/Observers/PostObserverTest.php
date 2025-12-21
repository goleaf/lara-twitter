<?php

namespace Tests\Unit\Observers;

use App\Events\NewPostCreated;
use App\Models\Block;
use App\Models\Follow;
use App\Models\Hashtag;
use App\Models\Mention;
use App\Models\Mute;
use App\Models\Post;
use App\Models\PostImage;
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
use Illuminate\Support\Facades\Storage;
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

    public function test_saving_sets_reply_like_false_for_plain_body(): void
    {
        $observer = app(PostObserver::class);

        $post = Post::factory()->make([
            'reply_to_id' => null,
            'body' => 'Hello world',
        ]);

        $observer->saving($post);

        $this->assertFalse($post->is_reply_like);
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

    public function test_saved_skips_mentions_when_disallowed(): void
    {
        Notification::fake();

        $author = User::factory()->create();
        $muted = User::factory()->create([
            'username' => 'bob',
            'notification_settings' => ['mentions' => false],
        ]);
        $blocked = User::factory()->create(['username' => 'carol']);

        Block::factory()->create([
            'blocker_id' => $blocked->id,
            'blocked_id' => $author->id,
        ]);

        $post = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
            'body' => 'Hello @bob @carol',
        ]));

        $parser = app(PostTextParser::class);
        $linkPreviews = Mockery::mock(PostLinkPreviewService::class);
        $linkPreviews->shouldReceive('extractFirstUrl')
            ->once()
            ->with($post->body)
            ->andReturn(null);

        $observer = new PostObserver($parser, $linkPreviews);
        $observer->saved($post);

        Notification::assertNotSentTo($muted, PostMentioned::class);
        Notification::assertNotSentTo($blocked, PostMentioned::class);
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

    public function test_saved_skips_link_preview_when_not_recent_or_changed(): void
    {
        $author = User::factory()->create();

        $post = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
            'body' => 'No links here',
        ]));
        $post->wasRecentlyCreated = false;
        $post->syncOriginal();

        $parser = app(PostTextParser::class);
        $linkPreviews = Mockery::mock(PostLinkPreviewService::class);
        $linkPreviews->shouldNotReceive('extractFirstUrl');

        $observer = new PostObserver($parser, $linkPreviews);
        $observer->saved($post);
    }

    public function test_saved_updates_link_preview_when_allowed(): void
    {
        $originalEnv = app('env');
        app()->instance('env', 'local');

        try {
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
            $linkPreviews->shouldReceive('fetch')
                ->once()
                ->with('https://example.com')
                ->andReturn([
                    'site_name' => 'Example',
                    'title' => 'Title',
                    'description' => 'Description',
                    'image_url' => 'https://example.com/image.png',
                ]);

            $observer = new PostObserver($parser, $linkPreviews);
            $observer->saved($post);

            $preview = PostLinkPreview::query()->where('post_id', $post->id)->firstOrFail();
            $this->assertSame('Example', $preview->site_name);
            $this->assertNotNull($preview->fetched_at);
        } finally {
            app()->instance('env', $originalEnv);
        }
    }

    public function test_saved_skips_fetch_when_preview_fresh_and_unchanged(): void
    {
        $originalEnv = app('env');
        app()->instance('env', 'local');

        try {
            $author = User::factory()->create();
            $post = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
                'body' => 'Check https://example.com',
            ]));

            PostLinkPreview::query()->create([
                'post_id' => $post->id,
                'url' => 'https://example.com',
                'fetched_at' => now(),
            ]);

            $parser = app(PostTextParser::class);
            $linkPreviews = Mockery::mock(PostLinkPreviewService::class);
            $linkPreviews->shouldReceive('extractFirstUrl')
                ->once()
                ->with($post->body)
                ->andReturn('https://example.com');
            $linkPreviews->shouldReceive('fetch')->never();

            $observer = new PostObserver($parser, $linkPreviews);
            $observer->saved($post);
        } finally {
            app()->instance('env', $originalEnv);
        }
    }

    public function test_saved_skips_fetch_when_no_data(): void
    {
        $originalEnv = app('env');
        app()->instance('env', 'local');

        try {
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
            $linkPreviews->shouldReceive('fetch')
                ->once()
                ->with('https://example.com')
                ->andReturn(null);

            $observer = new PostObserver($parser, $linkPreviews);
            $observer->saved($post);

            $preview = PostLinkPreview::query()->where('post_id', $post->id)->firstOrFail();
            $this->assertNull($preview->fetched_at);
        } finally {
            app()->instance('env', $originalEnv);
        }
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

    public function test_created_sends_quote_repost_notification(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['reposts' => true],
        ]);
        $reposter = User::factory()->create();

        $original = Post::withoutEvents(fn () => Post::factory()->for($author)->create());

        $quote = Post::withoutEvents(fn () => Post::factory()->for($reposter)->create([
            'repost_of_id' => $original->id,
            'reply_to_id' => null,
            'body' => 'Quote text',
        ]));

        $observer = app(PostObserver::class);
        $observer->created($quote);

        Notification::assertSentTo($author, PostReposted::class, function (PostReposted $notification) {
            return $notification->kind === 'quote';
        });
    }

    public function test_created_skips_repost_notification_when_disallowed(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['reposts' => true, 'only_verified' => true],
        ]);
        $reposter = User::factory()->create(['is_verified' => false]);

        $original = Post::withoutEvents(fn () => Post::factory()->for($author)->create());

        $repost = Post::withoutEvents(fn () => Post::factory()->for($reposter)->create([
            'repost_of_id' => $original->id,
            'reply_to_id' => null,
            'body' => '',
        ]));

        $observer = app(PostObserver::class);
        $observer->created($repost);

        Notification::assertNotSentTo($author, PostReposted::class);
    }

    public function test_created_skips_reply_notification_when_original_missing(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $replier = User::factory()->create();
        $reply = Post::factory()->make([
            'user_id' => $replier->id,
            'reply_to_id' => 9999,
        ]);

        $observer = app(PostObserver::class);
        $observer->created($reply);

        Notification::assertNothingSent();
    }

    public function test_created_skips_reply_notification_when_self_reply(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create();

        $original = Post::withoutEvents(fn () => Post::factory()->for($author)->create());
        $reply = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
            'reply_to_id' => $original->id,
        ]));

        $observer = app(PostObserver::class);
        $observer->created($reply);

        Notification::assertNotSentTo($author, PostReplied::class);
    }

    public function test_created_skips_reply_notification_when_blocked(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['replies' => true],
        ]);
        $replier = User::factory()->create();

        $original = Post::withoutEvents(fn () => Post::factory()->for($author)->create());

        Block::factory()->create([
            'blocker_id' => $replier->id,
            'blocked_id' => $author->id,
        ]);

        $reply = Post::withoutEvents(fn () => Post::factory()->for($replier)->create([
            'reply_to_id' => $original->id,
        ]));

        $observer = app(PostObserver::class);
        $observer->created($reply);

        Notification::assertNotSentTo($author, PostReplied::class);
    }

    public function test_created_skips_reply_notification_when_muted(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['replies' => true],
        ]);
        $replier = User::factory()->create();

        $original = Post::withoutEvents(fn () => Post::factory()->for($author)->create());

        Mute::factory()->create([
            'muter_id' => $author->id,
            'muted_id' => $replier->id,
        ]);

        $reply = Post::withoutEvents(fn () => Post::factory()->for($replier)->create([
            'reply_to_id' => $original->id,
        ]));

        $observer = app(PostObserver::class);
        $observer->created($reply);

        Notification::assertNotSentTo($author, PostReplied::class);
    }

    public function test_created_skips_reply_notification_when_disabled(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['replies' => false],
        ]);
        $replier = User::factory()->create();

        $original = Post::withoutEvents(fn () => Post::factory()->for($author)->create());
        $reply = Post::withoutEvents(fn () => Post::factory()->for($replier)->create([
            'reply_to_id' => $original->id,
        ]));

        $observer = app(PostObserver::class);
        $observer->created($reply);

        Notification::assertNotSentTo($author, PostReplied::class);
    }

    public function test_created_skips_reply_notification_when_actor_not_allowed(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create([
            'notification_settings' => ['replies' => true, 'only_verified' => true],
        ]);
        $replier = User::factory()->create(['is_verified' => false]);

        $original = Post::withoutEvents(fn () => Post::factory()->for($author)->create());
        $reply = Post::withoutEvents(fn () => Post::factory()->for($replier)->create([
            'reply_to_id' => $original->id,
        ]));

        $observer = app(PostObserver::class);
        $observer->created($reply);

        Notification::assertNotSentTo($author, PostReplied::class);
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

    public function test_created_skips_followers_notification_for_reply_like(): void
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
            'body' => '@bob reply like',
            'is_reply_like' => true,
        ]));

        $observer = app(PostObserver::class);
        $observer->created($post);

        Notification::assertNotSentTo($follower, FollowedUserPosted::class);
    }

    public function test_created_filters_followed_posts_notifications(): void
    {
        Event::fake([NewPostCreated::class]);
        Notification::fake();

        $author = User::factory()->create([
            'is_verified' => false,
            'avatar_path' => null,
            'email_verified_at' => null,
        ]);

        $allowed = User::factory()->create([
            'notification_settings' => ['followed_posts' => true],
        ]);
        $onlyVerified = User::factory()->create([
            'notification_settings' => ['followed_posts' => true, 'only_verified' => true],
        ]);
        $qualityFilter = User::factory()->create([
            'notification_settings' => ['followed_posts' => true, 'quality_filter' => true],
        ]);
        $blocked = User::factory()->create([
            'notification_settings' => ['followed_posts' => true],
        ]);
        $muted = User::factory()->create([
            'notification_settings' => ['followed_posts' => true],
        ]);
        $disabled = User::factory()->create([
            'notification_settings' => ['followed_posts' => false],
        ]);

        foreach ([$allowed, $onlyVerified, $qualityFilter, $blocked, $muted, $disabled] as $follower) {
            Follow::factory()->create([
                'follower_id' => $follower->id,
                'followed_id' => $author->id,
            ]);
        }

        Block::factory()->create([
            'blocker_id' => $author->id,
            'blocked_id' => $blocked->id,
        ]);

        Mute::factory()->create([
            'muter_id' => $muted->id,
            'muted_id' => $author->id,
        ]);

        $post = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
            'body' => 'Hello',
        ]));

        $observer = app(PostObserver::class);
        $observer->created($post);

        Notification::assertSentTo($allowed, FollowedUserPosted::class);
        Notification::assertNotSentTo($onlyVerified, FollowedUserPosted::class);
        Notification::assertNotSentTo($qualityFilter, FollowedUserPosted::class);
        Notification::assertNotSentTo($blocked, FollowedUserPosted::class);
        Notification::assertNotSentTo($muted, FollowedUserPosted::class);
        Notification::assertNotSentTo($disabled, FollowedUserPosted::class);
    }

    public function test_deleting_removes_media_and_retweets(): void
    {
        Storage::fake('public');
        config(['filesystems.media_disk' => 'public']);

        $author = User::factory()->create();
        $post = Post::withoutEvents(fn () => Post::factory()->for($author)->create([
            'video_path' => 'videos/test.mp4',
        ]));

        $image = PostImage::factory()->create([
            'post_id' => $post->id,
            'path' => 'images/test.jpg',
        ]);

        Storage::disk('public')->put($image->path, 'image');
        Storage::disk('public')->put($post->video_path, 'video');

        $retweet = Post::withoutEvents(fn () => Post::factory()->for(User::factory()->create())->create([
            'repost_of_id' => $post->id,
            'reply_to_id' => null,
            'body' => '',
        ]));

        $quote = Post::withoutEvents(fn () => Post::factory()->for(User::factory()->create())->create([
            'repost_of_id' => $post->id,
            'reply_to_id' => null,
            'body' => 'quote',
        ]));

        $observer = app(PostObserver::class);
        $observer->deleting($post);

        Storage::disk('public')->assertMissing($image->path);
        Storage::disk('public')->assertMissing($post->video_path);

        $this->assertFalse(Post::query()->whereKey($retweet->id)->exists());
        $this->assertTrue(Post::query()->whereKey($quote->id)->exists());
    }
}
