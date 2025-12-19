<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePinnedMediaVerifiedTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_shows_verified_badge(): void
    {
        $user = User::factory()->create(['username' => 'alice', 'is_verified' => true]);

        $this->get(route('profile.show', ['user' => $user]))
            ->assertOk()
            ->assertSee('Verified');
    }

    public function test_profile_shows_pinned_post_and_excludes_from_main_list(): void
    {
        $user = User::factory()->create(['username' => 'alice']);

        $pinned = Post::query()->create(['user_id' => $user->id, 'body' => 'Pinned post']);
        Post::query()->create(['user_id' => $user->id, 'body' => 'Other post']);

        $user->update(['pinned_post_id' => $pinned->id]);

        $response = $this->get(route('profile.show', ['user' => $user]));

        $response
            ->assertOk()
            ->assertSee('Pinned')
            ->assertSee('Pinned post')
            ->assertSee('Other post');

        // pinned content should only appear once on the page.
        $this->assertSame(1, substr_count($response->getContent(), 'Pinned post'));
    }

    public function test_media_tab_shows_posts_with_images_or_video(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['username' => 'alice']);

        $withImage = Post::query()->create(['user_id' => $user->id, 'body' => 'With image']);
        $withVideo = Post::query()->create(['user_id' => $user->id, 'body' => 'With video']);
        $withoutMedia = Post::query()->create(['user_id' => $user->id, 'body' => 'Without media']);

        $path = UploadedFile::fake()->image('one.jpg')->storePublicly("posts/{$withImage->id}", ['disk' => 'public']);
        $withImage->images()->create(['path' => $path, 'sort_order' => 0]);

        $videoPath = UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4')->storePublicly("posts/{$withVideo->id}", ['disk' => 'public']);
        $withVideo->update([
            'video_path' => $videoPath,
            'video_mime_type' => 'video/mp4',
        ]);

        $this->get(route('profile.media', ['user' => $user]))
            ->assertOk()
            ->assertSee('With image')
            ->assertSee('With video')
            ->assertDontSee('Without media');
    }
}
