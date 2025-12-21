<?php

namespace App\Pipelines;

use App\Dto\ProcessedImage;
use Closure;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ScaleImage
{
    protected int $maxWidth = 1920;

    protected int $maxHeight = 1920;

    public function handle(ProcessedImage $image, Closure $next): ProcessedImage
    {
        try {
            $manager = $this->getImageManager();
            $img = $manager->read($image->getFilePath());

            $scaledImage = $img->scaleDown($this->maxWidth, $this->maxHeight);
            $scaledImage->save();

            return $next($image);
        } catch (\Throwable $e) {
            // If scaling fails, continue with original image
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
}
