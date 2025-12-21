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

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'scheduled_for' => now()->addHours(fake()->numberBetween(1, 72)),
            'started_at' => null,
            'ended_at' => null,
        ]);
    }

    public function live(): static
    {
        return $this->state(fn () => [
            'scheduled_for' => null,
            'started_at' => now()->subMinutes(fake()->numberBetween(5, 180)),
            'ended_at' => null,
        ]);
    }

    public function ended(): static
    {
        return $this->state(function () {
            $started = now()->subHours(fake()->numberBetween(1, 24));

            return [
                'scheduled_for' => null,
                'started_at' => $started,
                'ended_at' => (clone $started)->addMinutes(fake()->numberBetween(10, 240)),
            ];
        });
    }
}
