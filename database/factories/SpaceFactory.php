<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'host_user_id' => User::factory(),
            'title' => fake()->sentence(fake()->numberBetween(2, 5)),
            'description' => fake()->optional()->text(240),
            'pinned_post_id' => null,
            'scheduled_for' => null,
            'recording_enabled' => false,
            'started_at' => null,
            'ended_at' => null,
            'recording_available_until' => null,
        ];
    }
}

