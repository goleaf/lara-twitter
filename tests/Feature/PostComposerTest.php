<?php

namespace Tests\Feature;

use App\Livewire\PostComposer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PostComposerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_post_with_images_and_extract_tags(): void
    {
        Storage::persistentFake('public');

        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        Livewire::actingAs($alice)
            ->test(PostComposer::class)
            ->set('body', 'Hey @bob check #Laravel')
            ->set('media', [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $post = $alice->posts()->firstOrFail();

        $this->assertDatabaseHas('hashtags', ['tag' => 'laravel']);
        $this->assertDatabaseHas('hashtag_post', ['post_id' => $post->id]);
        $this->assertDatabaseHas('mentions', [
            'post_id' => $post->id,
            'mentioned_user_id' => $bob->id,
        ]);

        $this->assertCount(2, $post->images);
        Storage::disk('public')->assertExists($post->images[0]->path);
        Storage::disk('public')->assertExists($post->images[1]->path);
    }

    public function test_authenticated_user_can_create_post_with_video(): void
    {
        Storage::persistentFake('public');

        $alice = User::factory()->create(['username' => 'alice']);

        Livewire::actingAs($alice)
            ->test(PostComposer::class)
            ->set('body', 'Video post')
            ->set('media', [
                UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $post = $alice->posts()->firstOrFail();

        $this->assertNotNull($post->video_path);
        $this->assertSame('video/mp4', $post->video_mime_type);
        Storage::disk('public')->assertExists($post->video_path);
    }

    public function test_post_requires_body_and_limits_images(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);

        Livewire::actingAs($alice)
            ->test(PostComposer::class)
            ->set('body', '')
            ->call('save')
            ->assertHasErrors(['body' => 'required']);

        Livewire::actingAs($alice)
            ->test(PostComposer::class)
            ->set('body', 'Valid')
            ->set('media', [
                UploadedFile::fake()->image('1.jpg'),
                UploadedFile::fake()->image('2.jpg'),
                UploadedFile::fake()->image('3.jpg'),
                UploadedFile::fake()->image('4.jpg'),
                UploadedFile::fake()->image('5.jpg'),
            ])
            ->call('save')
            ->assertHasErrors(['media']);
    }

    public function test_guest_cannot_create_post(): void
    {
        Livewire::test(PostComposer::class)
            ->set('body', 'Hello')
            ->call('save')
            ->assertStatus(403);
    }
}
