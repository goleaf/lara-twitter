<?php

namespace App\Livewire\Help;

use Livewire\Component;

class MutePage extends Component
{
    public function render()
    {
        return view('help.mute')->layout('layouts.app');
    }
}
