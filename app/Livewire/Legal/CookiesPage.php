<?php

namespace App\Livewire\Legal;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CookiesPage extends Component
{
    public function render()
    {
        return view('legal.cookies');
    }
}
