<?php

namespace App\Livewire;

use Livewire\Component;

class ProfileSettingsPage extends Component
{
    public function render()
    {
        return view('profile')->layout('layouts.app');
    }
}
