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
            ->assertSee('Direct Messages')
            ->assertSee('Hashtags')
            ->assertSee('Profile');

        $this->get(route('help.hashtags'))
            ->assertOk()
            ->assertSee('Hashtags')
            ->assertSee('Top')
            ->assertSee('Latest');

        $this->get(route('help.profile'))
            ->assertOk()
            ->assertSee('Profile')
            ->assertSee('Avatar')
            ->assertSee('Pinned post');

        $this->get(route('help.direct-messages'))
            ->assertOk()
            ->assertSee('Direct Messages')
            ->assertSee('Message requests')
            ->assertSee('Read receipts');
    }
}
