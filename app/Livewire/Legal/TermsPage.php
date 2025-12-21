<?php

namespace App\Livewire\Legal;

use Livewire\Component;

class TermsPage extends Component
{
    public function render()
    {
        return view('legal.terms')->layout('layouts.app');
    }
}
