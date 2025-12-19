<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MessageAttachment>
 */
class MessageAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mime = fake()->randomElement([
            'image/jpeg',
            'image/png',
            'image/webp',
            'video/mp4',
        ]);

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            default => 'bin',
        };

        return [
            'message_id' => Message::factory(),
            'path' => 'seed-dm/'.fake()->uuid().'.'.$extension,
            'mime_type' => $mime,
            'sort_order' => 0,
        ];
    }
}

