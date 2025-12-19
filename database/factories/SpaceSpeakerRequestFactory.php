<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\SpaceSpeakerRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpaceSpeakerRequest>
 */
class SpaceSpeakerRequestFactory extends Factory
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
            'status' => SpaceSpeakerRequest::STATUS_PENDING,
            'decided_by' => null,
            'decided_at' => null,
        ];
    }
}

