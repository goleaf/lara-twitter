<?php

namespace Tests\Unit\Services;

use App\Dto\ProcessedImage;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Pipeline\Pipeline;
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

        $result = $service->optimizeAndUpload($file, 'posts/1', 'public');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('path', $result);

        $path = $result['path'];
        $this->assertStringStartsWith('posts/1/', $path);
        $this->assertTrue(str_ends_with($path, '.webp') || str_ends_with($path, '.jpg'));
        Storage::disk('public')->assertExists($path);

        if ($result['thumbnail_path']) {
            Storage::disk('public')->assertExists($result['thumbnail_path']);
        }
    }

    public function test_optimize_and_upload_stores_thumbnail_when_generated(): void
    {
        Storage::fake('public');

        $processedFile = tempnam(sys_get_temp_dir(), 'img');
        $thumbnailFile = tempnam(sys_get_temp_dir(), 'thumb');

        file_put_contents($processedFile, 'image');
        file_put_contents($thumbnailFile, 'thumb');

        $processed = new ProcessedImage($processedFile, 'public', 'posts/1');
        $processed->setThumbnailPath($thumbnailFile);

        $this->bindPipelineReturning($processed);

        $service = new ImageService();
        $file = UploadedFile::fake()->image('photo.jpg', 1200, 800);

        $result = $service->optimizeAndUpload($file, 'posts/1', 'public');

        $this->assertNotNull($result['thumbnail_path']);
        Storage::disk('public')->assertExists($result['path']);
        Storage::disk('public')->assertExists($result['thumbnail_path']);
    }

    public function test_optimize_and_upload_uses_stream_fallback_on_error(): void
    {
        Storage::fake('public');

        $this->bindPipelineThrowing();

        $file = new StreamUploadedFile($this->makeTempFile(), 'stream.txt', 'text/plain', null, true);
        $service = new ImageService();

        $result = $service->optimizeAndUpload($file, 'streams', 'public');

        $this->assertStringStartsWith('streams/', $result['path']);
        $this->assertNull($result['thumbnail_path']);
        Storage::disk('public')->assertExists($result['path']);
    }

    public function test_optimize_and_upload_falls_back_when_contents_missing(): void
    {
        Storage::fake('public');

        $this->bindPipelineThrowing();

        $file = new MissingContentsUploadedFile($this->makeTempFile(), 'missing.txt', 'text/plain', null, true);
        $service = new ImageService();

        $result = $service->optimizeAndUpload($file, 'fallback', 'public');

        $this->assertStringStartsWith('fallback/', $result['path']);
        $this->assertNull($result['thumbnail_path']);
    }

    public function test_optimize_and_upload_falls_back_when_put_fails(): void
    {
        $this->bindPipelineThrowing();

        $file = new StoreBypassUploadedFile($this->makeTempFile(), 'fail.txt', 'text/plain', null, true);
        $disk = new class
        {
            public function path(string $path): string
            {
                return $path;
            }

            public function put(string $path, mixed $contents): bool
            {
                return false;
            }
        };

        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $service = new ImageService();

        $result = $service->optimizeAndUpload($file, 'fallback', 'public');

        $this->assertSame('fallback/'.$file->hashName(), $result['path']);
        $this->assertNull($result['thumbnail_path']);
    }

    public function test_optimize_and_upload_falls_back_for_non_images(): void
    {
        Storage::fake('public');

        $service = new ImageService();
        $file = UploadedFile::fake()->create('note.txt', 1, 'text/plain');

        $result = $service->optimizeAndUpload($file, 'notes', 'public');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('path', $result);
        $this->assertNull($result['thumbnail_path']);

        $path = $result['path'];
        $this->assertStringStartsWith('notes/', $path);
        $this->assertTrue(str_ends_with($path, '.txt'));
        Storage::disk('public')->assertExists($path);
    }

    private function bindPipelineReturning(ProcessedImage $processedImage): void
    {
        $this->app->bind(Pipeline::class, fn () => new StubPipeline($processedImage));
    }

    private function bindPipelineThrowing(): void
    {
        $this->app->bind(Pipeline::class, fn () => new StubPipeline(null, true));
    }

    private function makeTempFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($path, 'content');

        return $path;
    }
}

class StubPipeline
{
    public function __construct(private ?ProcessedImage $result, private bool $throw = false)
    {
    }

    public function send(mixed $passable): self
    {
        return $this;
    }

    public function through(array $pipes): self
    {
        return $this;
    }

    public function thenReturn(): ProcessedImage
    {
        if ($this->throw) {
            throw new \RuntimeException('pipeline failed');
        }

        return $this->result;
    }
}

class StreamUploadedFile extends UploadedFile
{
    public function readStream()
    {
        return fopen($this->getPathname(), 'r');
    }
}

class MissingContentsUploadedFile extends UploadedFile
{
    public function hashName($path = null): string
    {
        return $this->getClientOriginalName();
    }

    public function store($path = '', $options = [])
    {
        $stored = parent::store($path, $options);

        @unlink($this->getPathname());

        return $stored;
    }

    public function storePublicly($path = '', $options = [])
    {
        $disk = $options['disk'] ?? 'public';
        $stored = $path.'/'.$this->hashName();

        Storage::disk($disk)->put($stored, 'fallback');

        return $stored;
    }
}

class StoreBypassUploadedFile extends UploadedFile
{
    public function store($path = '', $options = [])
    {
        return trim($path, '/').'/'.$this->hashName();
    }

    public function storePublicly($path = '', $options = [])
    {
        return trim($path, '/').'/'.$this->hashName();
    }
}
