<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsPage extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
    }

    public function markAllRead(): void
    {
        abort_unless(Auth::check(), 403);

        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        $this->dispatch('$refresh');
    }

    public function getNotificationsProperty()
    {
        return Auth::user()
            ->notifications()
            ->latest()
            ->paginate(30);
    }

    public function render()
    {
        return view('livewire.notifications-page')->layout('layouts.app');
    }
}

