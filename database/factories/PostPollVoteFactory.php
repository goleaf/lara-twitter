<?php

namespace Database\Factories;

use App\Models\PostPoll;
use App\Models\PostPollOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostPollVote>
 */
class PostPollVoteFactory extends Factory
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
            'post_poll_option_id' => PostPollOption::factory(),
            'user_id' => User::factory(),
        ];
    }
}

