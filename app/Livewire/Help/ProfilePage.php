<?php

namespace App\Livewire\Help;

use Livewire\Component;

class ProfilePage extends Component
{
    public function render()
    {
        return view('help.profile')->layout('layouts.app');
    }
}
