<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Moment>
 */
class MomentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'title' => fake()->sentence(fake()->numberBetween(2, 5)),
            'description' => fake()->optional()->text(180),
            'cover_image_path' => fake()->optional(0.7)->passthrough('seed-moments/'.fake()->uuid().'.jpg'),
            'is_public' => true,
        ];
    }
}

