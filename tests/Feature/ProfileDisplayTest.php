<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_displays_profile_fields(): void
    {
        Storage::persistentFake('public');

        $user = User::factory()->create([
            'username' => 'alice',
            'bio' => 'Hello there',
            'location' => 'Bratislava',
            'website' => 'https://example.com',
            'header_path' => 'headers/1/header.jpg',
        ]);

        $response = $this->get(route('profile.show', ['user' => $user]));

        $response
            ->assertOk()
            ->assertSee('Hello there')
            ->assertSee('Bratislava')
            ->assertSee('example.com')
            ->assertSee('Joined');
    }
}

