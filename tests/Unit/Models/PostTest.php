<?php

namespace Tests\Unit\Models;

use App\Models\Block;
use App\Models\Hashtag;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_can_be_created(): void
    {
        $post = Post::factory()->create();

        $this->assertInstanceOf(Post::class, $post);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_post_has_factory(): void
    {
        $post = Post::factory()->make();

        $this->assertInstanceOf(Post::class, $post);
    }

    public function test_with_viewer_context_returns_query_when_viewer_is_null(): void
    {
        $query = Post::query();

        $this->assertSame($query, $query->withViewerContext(null));
    }

    public function test_can_be_replied_by_handles_null_blocked_and_unknown_policy(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'reply_policy' => Post::REPLY_EVERYONE,
        ]);

        $this->assertFalse($post->canBeRepliedBy(null));

        Block::factory()->create([
            'blocker_id' => $author->id,
            'blocked_id' => $viewer->id,
        ]);

        $this->assertFalse($post->canBeRepliedBy($viewer));

        Block::query()->delete();
        $post->reply_policy = 'unknown';
        $post->save();

        $this->assertTrue($post->canBeRepliedBy($viewer));
    }

    public function test_to_searchable_array_and_index_name(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        $hashtag = Hashtag::factory()->create(['tag' => 'laravel']);
        $post->hashtags()->attach($hashtag->id);

        Like::factory()->create([
            'user_id' => $liker->id,
            'post_id' => $post->id,
        ]);

        $searchable = $post->toSearchableArray();

        $this->assertSame($post->id, $searchable['id']);
        $this->assertSame('Hello world', $searchable['content']);
        $this->assertSame($author->name, $searchable['user_name']);
        $this->assertSame($author->username, $searchable['user_username']);
        $this->assertSame(['laravel'], $searchable['hashtags']);
        $this->assertSame($post->created_at?->timestamp, $searchable['created_at']);
        $this->assertSame(1, $searchable['likes_count']);
        $this->assertSame('posts_index', $post->searchableAs());
    }
}
