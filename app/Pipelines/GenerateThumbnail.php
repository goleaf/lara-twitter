<?php

namespace App\Pipelines;

use App\Dto\ProcessedImage;
use Closure;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class GenerateThumbnail
{
    protected int $thumbnailWidth = 300;

    protected int $thumbnailHeight = 300;

    public function handle(ProcessedImage $image, Closure $next): ProcessedImage
    {
        try {
            $manager = $this->getImageManager();
            $thumbnail = $manager->read($image->getFilePath())
                ->scaleDown($this->thumbnailWidth, $this->thumbnailHeight)
                ->toWebp(85);

            $thumbnailPath = $this->getThumbnailPath($image->getFilePath());
            $thumbnail->save($thumbnailPath);

            $image->setThumbnailPath($thumbnailPath);

            return $next($image);
        } catch (\Throwable $e) {
            // If thumbnail generation fails, continue without thumbnail
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

    private function getThumbnailPath(string $filePath): string
    {
        $pathInfo = pathinfo($filePath);
        $extension = $pathInfo['extension'] ?? 'webp';

        return $pathInfo['dirname'].'/'.$pathInfo['filename'].'_thumbnail.'.$extension;
    }
}
