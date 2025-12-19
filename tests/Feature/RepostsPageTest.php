<?php

namespace Tests\Feature;

use App\Livewire\RepostsPage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RepostsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_reposts_page_shows_retweeters_and_quotes(): void
    {
        $author = User::factory()->create(['username' => 'author']);
        $retweeter = User::factory()->create(['username' => 'bob']);
        $quoter = User::factory()->create(['username' => 'carol']);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Original',
        ]);

        Post::query()->create([
            'user_id' => $retweeter->id,
            'repost_of_id' => $post->id,
            'body' => '',
        ]);

        $quote = Post::query()->create([
            'user_id' => $quoter->id,
            'repost_of_id' => $post->id,
            'body' => 'My take',
        ]);

        Livewire::test(RepostsPage::class, ['post' => $post])
            ->assertSee('Retweets')
            ->assertSee('Quotes')
            ->assertSee('@bob')
            ->set('tab', 'quotes')
            ->assertSee('My take')
            ->assertSee('@carol')
            ->assertSee('Original')
            ->assertSee($quote->repostOf->user->name);
    }
}

