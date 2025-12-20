<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    public function optimizeAndUpload(UploadedFile $file, string $directory, ?string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.media_disk', 'public');
        $directory = trim($directory, '/');

        if (class_exists(ImageManager::class)) {
            try {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getPathname());
                $image = $image->scaleDown(2000, 2000);

                $encoded = $image->toJpeg(85);
                $filename = pathinfo($file->hashName(), PATHINFO_FILENAME).'.jpg';
                $path = $directory.'/'.$filename;

                if (Storage::disk($disk)->put($path, (string) $encoded)) {
                    return $path;
                }
            } catch (\Throwable) {
                // Fall back to storing the original upload.
            }
        }

        $path = $directory.'/'.$file->hashName();

        if (method_exists($file, 'readStream')) {
            $stream = $file->readStream();

            if (is_resource($stream)) {
                Storage::disk($disk)->put($path, $stream);
                fclose($stream);

                return $path;
            }
        }

        $contents = @file_get_contents($file->getPathname());

        if ($contents === false) {
            return $file->storePublicly($directory, ['disk' => $disk]);
        }

        if (Storage::disk($disk)->put($path, $contents)) {
            return $path;
        }

        return $file->storePublicly($directory, ['disk' => $disk]);
    }
}
