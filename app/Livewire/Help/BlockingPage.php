<?php

namespace App\Livewire\Help;

use Livewire\Component;

class BlockingPage extends Component
{
    public function render()
    {
        return view('help.blocking')->layout('layouts.app');
    }
}
