<?php

namespace App\Jobs;

use App\Dto\ProcessedImage;
use App\Models\PostImage;
use App\Pipelines\ConvertToWebP;
use App\Pipelines\GenerateThumbnail;
use App\Pipelines\ScaleImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Storage;

class OptimizeImageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PostImage $postImage,
        public string $disk
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = Storage::disk($this->disk)->path($this->postImage->path);

        if (! file_exists($fullPath)) {
            return;
        }

        try {
            $processedImage = new ProcessedImage($fullPath, $this->disk, dirname($this->postImage->path));

            $processedImage = app(Pipeline::class)
                ->send($processedImage)
                ->through([
                    ConvertToWebP::class,
                    ScaleImage::class,
                    GenerateThumbnail::class,
                ])
                ->thenReturn();

            // Update the main image path if it changed
            if ($processedImage->getFilePath() !== $fullPath) {
                $directory = dirname($this->postImage->path);
                $filename = pathinfo($this->postImage->path, PATHINFO_FILENAME);
                $extension = pathinfo($processedImage->getFilePath(), PATHINFO_EXTENSION) ?: 'webp';
                $newPath = $directory.'/'.$filename.'.'.$extension;

                $optimizedContent = file_get_contents($processedImage->getFilePath());
                Storage::disk($this->disk)->put($newPath, $optimizedContent);

                // Delete old file if different
                if ($newPath !== $this->postImage->path) {
                    Storage::disk($this->disk)->delete($this->postImage->path);
                }

                $this->postImage->update(['path' => $newPath]);
            }

            // Handle thumbnail
            if ($processedImage->getThumbnailPath() && file_exists($processedImage->getThumbnailPath())) {
                $directory = dirname($this->postImage->path);
                $filename = pathinfo($this->postImage->path, PATHINFO_FILENAME);
                $extension = pathinfo($processedImage->getThumbnailPath(), PATHINFO_EXTENSION) ?: 'webp';
                $thumbnailPath = $directory.'/'.$filename.'_thumbnail.'.$extension;

                $thumbnailContent = file_get_contents($processedImage->getThumbnailPath());
                Storage::disk($this->disk)->put($thumbnailPath, $thumbnailContent);

                $this->postImage->update(['thumbnail_path' => $thumbnailPath]);

                // Clean up temporary thumbnail
                @unlink($processedImage->getThumbnailPath());
            }

            // Clean up temporary file if different from original
            if ($processedImage->getFilePath() !== $fullPath && file_exists($processedImage->getFilePath())) {
                @unlink($processedImage->getFilePath());
            }
        } catch (\Throwable $e) {
            // Log error but don't fail the job
            report($e);
        }
    }
}
