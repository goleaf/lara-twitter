<?php

namespace App\Livewire\Legal;

use Livewire\Component;

class PrivacyPage extends Component
{
    public function render()
    {
        return view('legal.privacy')->layout('layouts.app');
    }
}
