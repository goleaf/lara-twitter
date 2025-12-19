<?php

namespace Tests\Feature;

use App\Livewire\PostCard;
use App\Models\Post;
use App\Models\PostLinkPreview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostLinkPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_link_preview_record_is_created_from_post_body(): void
    {
        $user = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $user->id,
            'body' => 'Check this out (https://example.com).',
        ]);

        $this->assertDatabaseHas('post_link_previews', [
            'post_id' => $post->id,
            'url' => 'https://example.com',
        ]);
    }

    public function test_post_card_renders_link_preview_card(): void
    {
        $user = User::factory()->create(['username' => 'alice']);

        $post = Post::query()->create([
            'user_id' => $user->id,
            'body' => 'Check https://example.com',
        ]);

        PostLinkPreview::query()->where('post_id', $post->id)->update([
            'site_name' => 'Example',
            'title' => 'Example Domain',
            'description' => 'Testing preview card rendering.',
            'image_url' => 'https://example.com/og.png',
        ]);

        Livewire::test(PostCard::class, ['post' => $post->load(['user', 'images', 'linkPreview'])])
            ->assertSee('Example Domain')
            ->assertSee('Testing preview card rendering.')
            ->assertSee('og.png', escape: false)
            ->assertSee("l/{$post->id}", escape: false);
    }
}

