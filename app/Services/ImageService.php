<?php

namespace App\Services;

use App\Dto\ProcessedImage;
use App\Pipelines\ConvertToWebP;
use App\Pipelines\GenerateThumbnail;
use App\Pipelines\ScaleImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Optimize and upload an image using the pipeline pattern.
     * Returns an array with 'path' and 'thumbnail_path' keys.
     *
     * @return array{path: string, thumbnail_path: ?string}
     */
    public function optimizeAndUpload(UploadedFile $file, string $directory, ?string $disk = null): array
    {
        $disk = $disk ?? config('filesystems.media_disk', 'public');
        $directory = trim($directory, '/');

        // Store the original file temporarily
        $tempPath = $file->store('temp', ['disk' => $disk]);
        $fullTempPath = Storage::disk($disk)->path($tempPath);

        try {
            // Create ProcessedImage DTO
            $processedImage = new ProcessedImage($fullTempPath, $disk, $directory);

            // Run through optimization pipeline
            $processedImage = app(Pipeline::class)
                ->send($processedImage)
                ->through([
                    ConvertToWebP::class,
                    ScaleImage::class,
                    GenerateThumbnail::class,
                ])
                ->thenReturn();

            // Generate final filename
            $filename = pathinfo($file->hashName(), PATHINFO_FILENAME);
            $extension = pathinfo($processedImage->getFilePath(), PATHINFO_EXTENSION) ?: 'webp';
            $finalFilename = $filename.'.'.$extension;
            $finalPath = $directory.'/'.$finalFilename;

            // Move optimized image to final location
            $optimizedContent = file_get_contents($processedImage->getFilePath());
            Storage::disk($disk)->put($finalPath, $optimizedContent);

            // Handle thumbnail if generated
            $thumbnailPath = null;
            if ($processedImage->getThumbnailPath() && file_exists($processedImage->getThumbnailPath())) {
                $thumbnailFilename = $filename.'_thumbnail.'.$extension;
                $thumbnailFinalPath = $directory.'/'.$thumbnailFilename;
                $thumbnailContent = file_get_contents($processedImage->getThumbnailPath());
                Storage::disk($disk)->put($thumbnailFinalPath, $thumbnailContent);
                $thumbnailPath = $thumbnailFinalPath;

                // Clean up temporary thumbnail
                @unlink($processedImage->getThumbnailPath());
            }

            // Clean up temporary files
            @unlink($fullTempPath);
            if ($processedImage->getFilePath() !== $fullTempPath) {
                @unlink($processedImage->getFilePath());
            }

            return [
                'path' => $finalPath,
                'thumbnail_path' => $thumbnailPath,
            ];
        } catch (\Throwable $e) {
            // Clean up temp file on error
            @unlink($fullTempPath);

            // Fall back to storing the original upload
            $path = $directory.'/'.$file->hashName();

            if (method_exists($file, 'readStream')) {
                $stream = $file->readStream();

                if (is_resource($stream)) {
                    Storage::disk($disk)->put($path, $stream);
                    fclose($stream);

                    return [
                        'path' => $path,
                        'thumbnail_path' => null,
                    ];
                }
            }

            $contents = @file_get_contents($file->getPathname());

            if ($contents === false) {
                $path = $file->storePublicly($directory, ['disk' => $disk]);

                return [
                    'path' => $path,
                    'thumbnail_path' => null,
                ];
            }

            if (Storage::disk($disk)->put($path, $contents)) {
                return [
                    'path' => $path,
                    'thumbnail_path' => null,
                ];
            }

            $path = $file->storePublicly($directory, ['disk' => $disk]);

            return [
                'path' => $path,
                'thumbnail_path' => null,
            ];
        }
    }
}
