<?php

namespace App\Livewire\Help;

use Livewire\Component;

class MentionsPage extends Component
{
    public function render()
    {
        return view('help.mentions')->layout('layouts.app');
    }
}
