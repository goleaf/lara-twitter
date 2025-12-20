<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_help_pages_render(): void
    {
        $this->get(route('help.index'))
            ->assertOk()
            ->assertSee('Help')
            ->assertSee('Block')
            ->assertSee('Mute')
            ->assertSee('Direct Messages')
            ->assertSee('Replies')
            ->assertSee('Hashtags')
            ->assertSee('Profile');

        $this->get(route('help.mute'))
            ->assertOk()
            ->assertSee('Mute')
            ->assertSee('What happens when you mute an account')
            ->assertSee('Muted words')
            ->assertSee('Muted accounts');

        $this->get(route('help.blocking'))
            ->assertOk()
            ->assertSee('Blocking')
            ->assertSee('What happens when you block someone')
            ->assertSee('Unblocking restores normal behavior');

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

        $this->get(route('help.replies'))
            ->assertOk()
            ->assertSee('Replies')
            ->assertSee('Conversation threads')
            ->assertSee('Reply permissions')
            ->assertSee('No one');
    }
}
