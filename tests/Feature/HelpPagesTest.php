<?php

namespace Tests\Feature;

use Tests\TestCase;

class HelpPagesTest extends TestCase
{
    public function test_help_pages_render(): void
    {
        $this->get(route('help.index'))
            ->assertOk()
            ->assertSee('Help')
            ->assertSee('Hashtags');

        $this->get(route('help.hashtags'))
            ->assertOk()
            ->assertSee('Hashtags')
            ->assertSee('Top')
            ->assertSee('Latest');
    }
}

