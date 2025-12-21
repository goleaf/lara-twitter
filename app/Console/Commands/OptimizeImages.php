<?php

namespace App\Console\Commands;

use App\Dto\ProcessedImage;
use App\Models\PostImage;
use App\Pipelines\ConvertToWebP;
use App\Pipelines\GenerateThumbnail;
use App\Pipelines\ScaleImage;
use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Storage;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize {--post-id= : Optimize images for a specific post} {--all : Optimize all images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize post images using the image optimization pipeline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = config('filesystems.media_disk', 'public');

        if ($this->option('post-id')) {
            $postId = (int) $this->option('post-id');
            $images = PostImage::where('post_id', $postId)->get();

            if ($images->isEmpty()) {
                $this->error("No images found for post ID: {$postId}");

                return Command::FAILURE;
            }

            $this->info("Optimizing {$images->count()} image(s) for post ID: {$postId}");
            $this->optimizeImages($images, $disk);

            return Command::SUCCESS;
        }

        if ($this->option('all')) {
            if (! $this->confirm('This will optimize all post images. Continue?')) {
                return Command::SUCCESS;
            }

            $images = PostImage::all();
            $this->info("Optimizing {$images->count()} image(s)");

            $this->optimizeImages($images, $disk);

            return Command::SUCCESS;
        }

        $this->error('Please specify --post-id=<id> or --all');

        return Command::FAILURE;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, PostImage>  $images
     */
    private function optimizeImages($images, string $disk): void
    {
        $bar = $this->output->createProgressBar($images->count());
        $bar->start();

        foreach ($images as $postImage) {
            $fullPath = Storage::disk($disk)->path($postImage->path);

            if (! file_exists($fullPath)) {
                $bar->advance();

                continue;
            }

            try {
                $processedImage = new ProcessedImage($fullPath, $disk, dirname($postImage->path));

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
                    $directory = dirname($postImage->path);
                    $filename = pathinfo($postImage->path, PATHINFO_FILENAME);
                    $extension = pathinfo($processedImage->getFilePath(), PATHINFO_EXTENSION) ?: 'webp';
                    $newPath = $directory.'/'.$filename.'.'.$extension;

                    $optimizedContent = file_get_contents($processedImage->getFilePath());
                    Storage::disk($disk)->put($newPath, $optimizedContent);

                    // Delete old file if different
                    if ($newPath !== $postImage->path) {
                        Storage::disk($disk)->delete($postImage->path);
                    }

                    $postImage->update(['path' => $newPath]);
                }

                // Handle thumbnail
                if ($processedImage->getThumbnailPath() && file_exists($processedImage->getThumbnailPath())) {
                    $directory = dirname($postImage->path);
                    $filename = pathinfo($postImage->path, PATHINFO_FILENAME);
                    $extension = pathinfo($processedImage->getThumbnailPath(), PATHINFO_EXTENSION) ?: 'webp';
                    $thumbnailPath = $directory.'/'.$filename.'_thumbnail.'.$extension;

                    $thumbnailContent = file_get_contents($processedImage->getThumbnailPath());
                    Storage::disk($disk)->put($thumbnailPath, $thumbnailContent);

                    $postImage->update(['thumbnail_path' => $thumbnailPath]);

                    // Clean up temporary thumbnail
                    @unlink($processedImage->getThumbnailPath());
                }

                // Clean up temporary file if different from original
                if ($processedImage->getFilePath() !== $fullPath && file_exists($processedImage->getFilePath())) {
                    @unlink($processedImage->getFilePath());
                }
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Failed to optimize image {$postImage->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Image optimization completed!');
    }
}
