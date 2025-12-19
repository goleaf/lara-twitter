<?php

namespace Database\Factories;

use App\Models\PostPoll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostPollOption>
 */
class PostPollOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_poll_id' => PostPoll::factory(),
            'option_text' => fake()->words(fake()->numberBetween(1, 3), true),
            'sort_order' => 0,
        ];
    }
}

