<?php

namespace App\Livewire\Legal;

use Livewire\Component;

class CookiesPage extends Component
{
    public function render()
    {
        return view('legal.cookies')->layout('layouts.app');
    }
}
