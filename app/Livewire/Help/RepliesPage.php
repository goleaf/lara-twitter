<?php

namespace App\Livewire\Help;

use Livewire\Component;

class RepliesPage extends Component
{
    public function render()
    {
        return view('help.replies')->layout('layouts.app');
    }
}
