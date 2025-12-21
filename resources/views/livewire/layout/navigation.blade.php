<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav aria-label="Primary">
    <button type="button" wire:click="logout" class="btn btn-ghost btn-sm">
        {{ __('Log Out') }}
    </button>
</nav>
