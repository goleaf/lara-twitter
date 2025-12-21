<?php

namespace App\Livewire\Legal;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AboutPage extends Component
{
    public function render()
    {
        return view('legal.about');
    }
}
