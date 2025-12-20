<?php

namespace Tests\Unit\Services;

use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_optimize_and_upload_stores_optimized_image(): void
    {
        Storage::fake('public');

        $service = new ImageService();
        $file = UploadedFile::fake()->image('photo.jpg', 1200, 800);

        $path = $service->optimizeAndUpload($file, 'posts/1', 'public');

        $this->assertStringStartsWith('posts/1/', $path);
        $this->assertTrue(str_ends_with($path, '.jpg'));
        Storage::disk('public')->assertExists($path);
    }

    public function test_optimize_and_upload_falls_back_for_non_images(): void
    {
        Storage::fake('public');

        $service = new ImageService();
        $file = UploadedFile::fake()->create('note.txt', 1, 'text/plain');

        $path = $service->optimizeAndUpload($file, 'notes', 'public');

        $this->assertStringStartsWith('notes/', $path);
        $this->assertTrue(str_ends_with($path, '.txt'));
        Storage::disk('public')->assertExists($path);
    }
}
