<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProfileSettingsPage extends Component
{
    public function render(): View
    {
        return view('livewire.profile-settings-page');
    }
}
