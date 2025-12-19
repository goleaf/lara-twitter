<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MutedTerm>
 */
class MutedTermFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'term' => fake()->words(fake()->numberBetween(1, 2), true),
            'whole_word' => fake()->boolean(),
            'only_non_followed' => fake()->boolean(),
            'mute_timeline' => fake()->boolean(70),
            'mute_notifications' => fake()->boolean(70),
            'expires_at' => fake()->optional(0.5)->dateTimeBetween('-14 days', '+14 days'),
        ];
    }
}

