<?php

namespace Tests\Feature;

use App\Livewire\BookmarksPage;
use App\Livewire\PostCard;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

