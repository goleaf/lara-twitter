<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hashtags = collect(fake()->words(fake()->numberBetween(0, 3)))
            ->map(fn (string $w) => '#'.fake()->lexify(substr($w, 0, 10)))
            ->values()
            ->all();

        return [
            'user_id' => User::factory(),
            'reply_to_id' => null,
            'body' => trim(fake()->sentence(fake()->numberBetween(6, 18)).' '.implode(' ', $hashtags)),
        ];
    }
}
