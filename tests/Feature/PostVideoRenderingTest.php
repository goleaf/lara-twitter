<?php

namespace Tests\Feature;

use App\Livewire\PostCard;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PostVideoRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_card_renders_video_when_post_has_video(): void
    {
        Storage::persistentFake('public');

        $user = User::factory()->create(['username' => 'alice']);

        $post = Post::query()->create([
            'user_id' => $user->id,
            'body' => 'Video post',
        ]);

        $path = "posts/{$post->id}/clip.mp4";
        Storage::disk('public')->put($path, 'x');
        $post->update([
            'video_path' => $path,
            'video_mime_type' => 'video/mp4',
        ]);

        Livewire::test(PostCard::class, ['post' => $post->load(['user', 'images'])])
            ->assertSee('<video', escape: false)
            ->assertSee('clip.mp4', escape: false);
    }
}

