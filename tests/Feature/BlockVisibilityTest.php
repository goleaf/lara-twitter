<?php

namespace Tests\Feature;

use App\Livewire\PostCard;
use App\Livewire\ProfilePage;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlockVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_block_hides_posts_from_search_hashtags_mentions_and_post_replies(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);

        $taggedByAlice = Post::query()->create(['user_id' => $alice->id, 'body' => 'From alice #tag @bob']);
        $taggedByCarol = Post::query()->create(['user_id' => $carol->id, 'body' => 'From carol #tag']);

        $parent = Post::query()->create(['user_id' => $carol->id, 'body' => 'Parent post']);
        Post::query()->create(['user_id' => $alice->id, 'reply_to_id' => $parent->id, 'body' => 'Reply from alice']);

        // sanity: guests can see public content
        $this->get(route('hashtags.show', ['tag' => 'tag']))
            ->assertOk()
            ->assertSee('From alice')
            ->assertSee('From carol');

        Livewire::actingAs($bob)
            ->test(ProfilePage::class, ['user' => $alice])
            ->call('toggleBlock')
            ->assertHasNoErrors();

        $this->actingAs($bob)->get(route('search', ['q' => 'From', 'type' => 'posts']))
            ->assertOk()
            ->assertSee('From carol')
            ->assertDontSee('From alice');

        $this->actingAs($bob)->get(route('hashtags.show', ['tag' => 'tag']))
            ->assertOk()
            ->assertSee('From carol')
            ->assertDontSee('From alice');

        $this->actingAs($bob)->get(route('mentions'))
            ->assertOk()
            ->assertDontSee('From alice');

        $this->actingAs($bob)->get(route('posts.show', $parent))
            ->assertOk()
            ->assertDontSee('Reply from alice');

        // avoid unused variable (ensures post created)
        $this->assertNotNull($taggedByAlice->id);
        $this->assertNotNull($taggedByCarol->id);
    }

    public function test_block_prevents_interactions_like_repost_bookmark_and_quote(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create(['user_id' => $alice->id, 'body' => 'Hello world']);

        Livewire::actingAs($bob)
            ->test(ProfilePage::class, ['user' => $alice])
            ->call('toggleBlock')
            ->assertHasNoErrors();

        Livewire::actingAs($bob)
            ->test(PostCard::class, ['post' => $post->fresh()])
            ->call('toggleLike')
            ->assertStatus(403);

        Livewire::actingAs($bob)
            ->test(PostCard::class, ['post' => $post->fresh()])
            ->call('toggleRepost')
            ->assertStatus(403);

        Livewire::actingAs($bob)
            ->test(PostCard::class, ['post' => $post->fresh()])
            ->call('toggleBookmark')
            ->assertStatus(403);

        Livewire::actingAs($bob)
            ->test(PostCard::class, ['post' => $post->fresh()])
            ->set('quote_body', 'My quote')
            ->call('quoteRepost')
            ->assertStatus(403);
    }

    public function test_block_hides_blocked_users_in_like_and_repost_lists(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);

        $post = Post::query()->create(['user_id' => $carol->id, 'body' => 'Hello']);

        Like::query()->create(['user_id' => $alice->id, 'post_id' => $post->id]);
        Post::query()->create(['user_id' => $alice->id, 'repost_of_id' => $post->id, 'body' => '']);

        Livewire::actingAs($bob)
            ->test(ProfilePage::class, ['user' => $alice])
            ->call('toggleBlock');

        $this->actingAs($bob)->get(route('posts.likes', $post))
            ->assertOk()
            ->assertDontSee('alice');

        $this->actingAs($bob)->get(route('posts.reposts', $post))
            ->assertOk()
            ->assertDontSee('alice');
    }
}
