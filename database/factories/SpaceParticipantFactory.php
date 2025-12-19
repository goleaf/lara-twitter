<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpaceParticipant>
 */
class SpaceParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'space_id' => Space::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement(['listener', 'speaker', 'cohost']),
            'joined_at' => fake()->optional()->dateTimeBetween('-2 days', 'now'),
            'left_at' => null,
        ];
    }
}

