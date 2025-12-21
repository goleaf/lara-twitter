<?php

namespace App\Livewire\Help;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DirectMessagesPage extends Component
{
    public function render()
    {
        return view('help.direct-messages');
    }
}
