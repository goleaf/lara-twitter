<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostLinkPreview>
 */
class PostLinkPreviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'url' => fake()->url(),
            'site_name' => fake()->optional()->company(),
            'title' => fake()->optional()->sentence(fake()->numberBetween(3, 6)),
            'description' => fake()->optional()->text(140),
            'image_url' => fake()->optional()->imageUrl(800, 400),
            'fetched_at' => fake()->optional()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}

