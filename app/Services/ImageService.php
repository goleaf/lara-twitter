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

        if (class_exists(ImageManager::class)) {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getPathname());
            $image = $image->scaleDown(2000, 2000);

            $encoded = $image->toJpeg(85);
            $filename = pathinfo($file->hashName(), PATHINFO_FILENAME).'.jpg';
            $path = trim($directory, '/').'/'.$filename;

            Storage::disk($disk)->put($path, (string) $encoded);

            return $path;
        }

        return $file->storePublicly($directory, ['disk' => $disk]);
    }
}
