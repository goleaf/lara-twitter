<?php

namespace App\Livewire\Help;

use Livewire\Component;

class IndexPage extends Component
{
    public function render()
    {
        return view('help.index')->layout('layouts.app');
    }
}
