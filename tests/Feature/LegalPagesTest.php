<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_render(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Terms of Service')
            ->assertSee('Using the service');

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Data we collect');

        $this->get(route('cookies'))
            ->assertOk()
            ->assertSee('Cookies and Tracking')
            ->assertSee('Essential cookies');

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('MiniTwitter')
            ->assertSee('What you can do');
    }
}
