<?php

namespace Tests\Feature;

use App\Livewire\BookmarksPage;
use App\Livewire\PostCard;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class BookmarksTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookmarks_page_requires_auth(): void
    {
        $this->get(route('bookmarks'))->assertRedirect('/login');
    }

    public function test_user_can_bookmark_and_see_it_in_bookmarks_page_only_for_them(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $viewer = User::factory()->create(['username' => 'bob']);
        $other = User::factory()->create(['username' => 'carol']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'A post',
        ]);

        Livewire::actingAs($viewer)
            ->test(PostCard::class, ['post' => $post])
            ->call('toggleBookmark')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $viewer->id,
            'post_id' => $post->id,
        ]);

        $response = $this->actingAs($viewer)->get(route('bookmarks'));
        $response->assertOk()->assertSee('A post');

        $response = $this->actingAs($other)->get(route('bookmarks'));
        $response->assertOk()->assertDontSee('A post');
    }

    public function test_user_can_bookmark_a_reply_and_see_it_in_bookmarks_page(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $viewer = User::factory()->create(['username' => 'bob']);

        $parent = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Parent post',
        ]);

        $reply = Post::query()->create([
            'user_id' => $viewer->id,
            'reply_to_id' => $parent->id,
            'body' => 'A reply',
        ]);

        Livewire::actingAs($viewer)
            ->test(PostCard::class, ['post' => $reply])
            ->call('toggleBookmark')
            ->assertHasNoErrors();

        $this->actingAs($viewer)
            ->get(route('bookmarks'))
            ->assertOk()
            ->assertSee('A reply');
    }

    public function test_user_can_bookmark_a_reply_like_post_and_see_it_in_bookmarks_page(): void
    {
        $author = User::factory()->create(['username' => 'alice']);
        $viewer = User::factory()->create(['username' => 'bob']);

        $replyLike = Post::query()->create([
            'user_id' => $author->id,
            'body' => '@bob hello there',
        ]);

        Livewire::actingAs($viewer)
            ->test(PostCard::class, ['post' => $replyLike])
            ->call('toggleBookmark')
            ->assertHasNoErrors();

        $this->actingAs($viewer)
            ->get(route('bookmarks'))
            ->assertOk()
            ->assertSee('hello there');
    }

    public function test_bookmarking_does_not_notify_the_post_author(): void
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'alice']);
        $viewer = User::factory()->create(['username' => 'bob']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'A post',
        ]);

        Livewire::actingAs($viewer)
            ->test(PostCard::class, ['post' => $post])
            ->call('toggleBookmark')
            ->assertHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_bookmarks_are_ordered_by_most_recent_bookmark_first(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $p1 = Post::query()->create(['user_id' => $author->id, 'body' => 'First']);
        $p2 = Post::query()->create(['user_id' => $author->id, 'body' => 'Second']);

        $user->bookmarkedPosts()->attach($p1->id, [
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $user->bookmarkedPosts()->attach($p2->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('bookmarks'))
            ->assertOk()
            ->assertSeeInOrder(['Second', 'First']);
    }
}
