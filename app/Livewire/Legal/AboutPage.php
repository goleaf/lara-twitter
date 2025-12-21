<?php

namespace App\Livewire\Legal;

use Livewire\Component;

class AboutPage extends Component
{
    public function render()
    {
        return view('legal.about')->layout('layouts.app');
    }
}
