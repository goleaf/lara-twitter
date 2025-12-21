<?php

namespace App\Pipelines;

use App\Dto\ProcessedImage;
use Closure;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ConvertToWebP
{
    public function handle(ProcessedImage $image, Closure $next): ProcessedImage
    {
        try {
            $manager = $this->getImageManager();
            $img = $manager->read($image->getFilePath());

            $webpImage = $img->toWebp(85);
            $webpPath = $this->getWebpPath($image->getFilePath());
            $webpImage->save($webpPath);

            $image->setFilePath($webpPath);

            return $next($image);
        } catch (\Throwable $e) {
            // If conversion fails, continue with original image
            return $next($image);
        }
    }

    private function getImageManager(): ImageManager
    {
        // Prefer Imagick if available, fallback to GD
        if (extension_loaded('imagick')) {
            return new ImageManager(new ImagickDriver);
        }

        return new ImageManager(new GdDriver);
    }

    private function getWebpPath(string $filePath): string
    {
        $pathInfo = pathinfo($filePath);
        $extension = $pathInfo['extension'] ?? '';

        // If already webp, return as is
        if (strtolower($extension) === 'webp') {
            return $filePath;
        }

        return $pathInfo['dirname'].'/'.$pathInfo['filename'].'.webp';
    }
}
