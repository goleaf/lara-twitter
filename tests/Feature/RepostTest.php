<?php

namespace Tests\Feature;

use App\Livewire\PostCard;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RepostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retweet_and_unretweet(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $retweeter = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        Livewire::actingAs($retweeter)
            ->test(PostCard::class, ['post' => $post->fresh()])
            ->call('toggleRepost');

        $this->assertDatabaseHas('posts', [
            'user_id' => $retweeter->id,
            'repost_of_id' => $post->id,
            'body' => '',
        ]);

        Livewire::actingAs($retweeter)
            ->test(PostCard::class, ['post' => $post->fresh()])
            ->call('toggleRepost');

        $this->assertDatabaseMissing('posts', [
            'user_id' => $retweeter->id,
            'repost_of_id' => $post->id,
            'body' => '',
        ]);
    }

    public function test_user_can_quote_tweet(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $quoter = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original post #laravel',
        ]);

        Livewire::actingAs($quoter)
            ->test(PostCard::class, ['post' => $post->fresh()])
            ->set('quote_body', 'My thoughts @alice #Nice')
            ->call('quoteRepost')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $quoter->id,
            'repost_of_id' => $post->id,
            'body' => 'My thoughts @alice #Nice',
        ]);

        $quote = Post::query()
            ->where('user_id', $quoter->id)
            ->where('repost_of_id', $post->id)
            ->where('body', 'My thoughts @alice #Nice')
            ->firstOrFail();

        $this->assertDatabaseHas('hashtags', ['tag' => 'nice']);
        $this->assertDatabaseHas('mentions', [
            'post_id' => $quote->id,
            'mentioned_user_id' => $author->id,
        ]);
    }

    public function test_timeline_renders_retweet_label(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $retweeter = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        Post::query()->create([
            'user_id' => $retweeter->id,
            'repost_of_id' => $post->id,
            'body' => '',
        ]);

        $response = $this->get(route('timeline'));

        $response
            ->assertOk()
            ->assertSee('Retweeted by')
            ->assertSee('@bob');
    }
}

