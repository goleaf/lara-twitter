<?php

namespace App\Livewire\Help;

use Livewire\Component;

class LikesPage extends Component
{
    public function render()
    {
        return view('help.likes')->layout('layouts.app');
    }
}
