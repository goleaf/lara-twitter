<?php

namespace Database\Factories;

use App\Models\Moment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MomentItem>
 */
class MomentItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'moment_id' => Moment::factory(),
            'post_id' => Post::factory(),
            'caption' => fake()->optional()->text(120),
            'sort_order' => 0,
        ];
    }
}

