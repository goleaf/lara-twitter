<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $username = Str::lower(fake()->unique()->bothify('user_##??##??##'));

        return [
            'name' => fake()->name(),
            'username' => $username,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'bio' => fake()->optional()->text(120),
            'is_admin' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'email_verified_at' => $attributes['email_verified_at'] ?? now(),
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn () => [
            'is_premium' => true,
        ]);
    }

    public function withProfile(): static
    {
        return $this->state(fn () => [
            'avatar_path' => 'seed-avatars/'.fake()->uuid().'.jpg',
            'header_path' => 'seed-headers/'.fake()->uuid().'.jpg',
            'bio' => fake()->text(120),
            'location' => fake()->city(),
            'website' => fake()->url(),
        ]);
    }
}
