<?php

namespace App\Livewire\Help;

use Livewire\Component;

class DirectMessagesPage extends Component
{
    public function render()
    {
        return view('help.direct-messages')->layout('layouts.app');
    }
}
