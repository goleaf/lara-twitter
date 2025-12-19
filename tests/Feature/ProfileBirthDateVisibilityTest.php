<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileBirthDateVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_shows_birth_date_when_public(): void
    {
        $user = User::factory()->create([
            'username' => 'alice',
            'birth_date' => '1990-01-02',
            'birth_date_visibility' => User::BIRTH_DATE_PUBLIC,
        ]);

        $this->get(route('profile.show', ['user' => $user]))
            ->assertOk()
            ->assertSee('Born Jan 2, 1990');
    }

    public function test_profile_hides_birth_date_when_private_from_guest(): void
    {
        $user = User::factory()->create([
            'username' => 'alice',
            'birth_date' => '1990-01-02',
            'birth_date_visibility' => User::BIRTH_DATE_PRIVATE,
        ]);

        $this->get(route('profile.show', ['user' => $user]))
            ->assertOk()
            ->assertDontSee('Born Jan 2, 1990');
    }

    public function test_profile_shows_birth_date_when_private_to_self(): void
    {
        $user = User::factory()->create([
            'username' => 'alice',
            'birth_date' => '1990-01-02',
            'birth_date_visibility' => User::BIRTH_DATE_PRIVATE,
        ]);

        $this->actingAs($user)
            ->get(route('profile.show', ['user' => $user]))
            ->assertOk()
            ->assertSee('Born Jan 2, 1990');
    }

    public function test_profile_shows_birth_date_to_followers_when_followers_only(): void
    {
        $alice = User::factory()->create([
            'username' => 'alice',
            'birth_date' => '1990-01-02',
            'birth_date_visibility' => User::BIRTH_DATE_FOLLOWERS,
        ]);

        $bob = User::factory()->create(['username' => 'bob']);

        $this->actingAs($bob)
            ->get(route('profile.show', ['user' => $alice]))
            ->assertOk()
            ->assertDontSee('Born Jan 2, 1990');

        $bob->following()->attach($alice->id);

        $this->actingAs($bob)
            ->get(route('profile.show', ['user' => $alice]))
            ->assertOk()
            ->assertSee('Born Jan 2, 1990');
    }
}

