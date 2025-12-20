<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class ValidPostMedia implements Rule
{
    private const MAX_IMAGES = 4;
    private const MAX_IMAGE_BYTES = 4194304;
    private const MAX_VIDEO_BYTES = 52428800;

    /** @var array<int, string> */
    private array $imageMimes = [
        'image/jpeg',
        'image/png',
        'image/bmp',
        'image/webp',
        'image/gif',
        'image/svg+xml',
    ];

    /** @var array<int, string> */
    private array $videoMimes = [
        'video/mp4',
        'video/webm',
    ];

    private string $message = 'Choose only images or a single video.';

    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        if (! is_array($value)) {
            $this->message = 'Media must be a list of files.';

            return false;
        }

        $files = array_values(array_filter($value));
        if (empty($files)) {
            return true;
        }

        $images = [];
        $videos = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                $this->message = 'Media must be valid files.';

                return false;
            }

            $mime = (string) ($file->getMimeType() ?? '');

            if (in_array($mime, $this->imageMimes, true)) {
                $images[] = $file;
                continue;
            }

            if (in_array($mime, $this->videoMimes, true)) {
                $videos[] = $file;
                continue;
            }

            $this->message = 'Media must be images or MP4/WebM videos.';

            return false;
        }

        if (! empty($images) && ! empty($videos)) {
            $this->message = 'Choose only images or a single video.';

            return false;
        }

        if (count($videos) > 1) {
            $this->message = 'Only one video is allowed.';

            return false;
        }

        if (count($images) > self::MAX_IMAGES) {
            $this->message = 'Up to 4 images or 1 video.';

            return false;
        }

        foreach ($images as $image) {
            if ($image->getSize() > self::MAX_IMAGE_BYTES) {
                $this->message = 'Images must be 4MB or smaller.';

                return false;
            }
        }

        foreach ($videos as $video) {
            if ($video->getSize() > self::MAX_VIDEO_BYTES) {
                $this->message = 'Video must be 50MB or smaller.';

                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }
}
