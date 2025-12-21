<?php

namespace App\Livewire\Help;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class HashtagsPage extends Component
{
    public function render()
    {
        return view('help.hashtags');
    }
}
