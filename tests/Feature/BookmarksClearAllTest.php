<?php

namespace Tests\Feature;

use App\Livewire\BookmarksPage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookmarksClearAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clear_all_bookmarks(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $p1 = Post::query()->create(['user_id' => $author->id, 'body' => 'One']);
        $p2 = Post::query()->create(['user_id' => $author->id, 'body' => 'Two']);

        $user->bookmarkedPosts()->attach([$p1->id, $p2->id]);

        Livewire::actingAs($user)
            ->test(BookmarksPage::class)
            ->call('clearAll');

        $this->assertDatabaseMissing('bookmarks', ['user_id' => $user->id, 'post_id' => $p1->id]);
        $this->assertDatabaseMissing('bookmarks', ['user_id' => $user->id, 'post_id' => $p2->id]);
    }
}

