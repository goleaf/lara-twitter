<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidPostMedia;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class ValidPostMediaTest extends TestCase
{
    public function test_passes_when_media_is_empty(): void
    {
        $rule = new ValidPostMedia();

        $this->assertTrue($rule->passes('media', null));
        $this->assertTrue($rule->passes('media', []));
        $this->assertTrue($rule->passes('media', [null]));
    }

    public function test_fails_when_media_is_not_array(): void
    {
        $rule = new ValidPostMedia();

        $this->assertFalse($rule->passes('media', 'nope'));
        $this->assertSame('Media must be a list of files.', $rule->message());
    }

    public function test_fails_when_media_contains_invalid_items(): void
    {
        $rule = new ValidPostMedia();

        $this->assertFalse($rule->passes('media', ['nope']));
        $this->assertSame('Media must be valid files.', $rule->message());
    }

    public function test_fails_for_unsupported_mime_types(): void
    {
        $rule = new ValidPostMedia();
        $file = UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf');

        $this->assertFalse($rule->passes('media', [$file]));
        $this->assertSame('Media must be images or MP4/WebM videos.', $rule->message());
    }

    public function test_fails_when_mixing_images_and_video(): void
    {
        $rule = new ValidPostMedia();
        $image = UploadedFile::fake()->image('one.jpg');
        $video = UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4');

        $this->assertFalse($rule->passes('media', [$image, $video]));
        $this->assertSame('Choose only images or a single video.', $rule->message());
    }

    public function test_fails_with_more_than_one_video(): void
    {
        $rule = new ValidPostMedia();
        $videoA = UploadedFile::fake()->create('a.mp4', 100, 'video/mp4');
        $videoB = UploadedFile::fake()->create('b.mp4', 100, 'video/mp4');

        $this->assertFalse($rule->passes('media', [$videoA, $videoB]));
        $this->assertSame('Only one video is allowed.', $rule->message());
    }

    public function test_fails_with_too_many_images(): void
    {
        $rule = new ValidPostMedia();

        $images = [
            UploadedFile::fake()->image('1.jpg'),
            UploadedFile::fake()->image('2.jpg'),
            UploadedFile::fake()->image('3.jpg'),
            UploadedFile::fake()->image('4.jpg'),
            UploadedFile::fake()->image('5.jpg'),
        ];

        $this->assertFalse($rule->passes('media', $images));
        $this->assertSame('Up to 4 images or 1 video.', $rule->message());
    }

    public function test_fails_when_image_is_too_large(): void
    {
        $rule = new ValidPostMedia();
        $image = UploadedFile::fake()->image('big.jpg')->size(5000);

        $this->assertFalse($rule->passes('media', [$image]));
        $this->assertSame('Images must be 4MB or smaller.', $rule->message());
    }

    public function test_fails_when_video_is_too_large(): void
    {
        $rule = new ValidPostMedia();
        $video = UploadedFile::fake()->create('big.mp4', 60000, 'video/mp4');

        $this->assertFalse($rule->passes('media', [$video]));
        $this->assertSame('Video must be 50MB or smaller.', $rule->message());
    }

    public function test_passes_with_valid_images_or_video(): void
    {
        $rule = new ValidPostMedia();

        $images = [
            UploadedFile::fake()->image('1.jpg'),
            UploadedFile::fake()->image('2.jpg'),
        ];
        $video = UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4');

        $this->assertTrue($rule->passes('media', $images));
        $this->assertTrue($rule->passes('media', [$video]));
    }
}
