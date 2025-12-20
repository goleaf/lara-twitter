<?php

namespace App\Livewire\Notifications;

use App\Services\NotificationVisibilityService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBadge extends Component
{
    public bool $inline = false;

    public function render()
    {
        $count = 0;

        if (Auth::check()) {
            $count = app(NotificationVisibilityService::class)
                ->visibleUnreadCount(Auth::user());
        }

        return view('livewire.notifications.notification-badge', [
            'count' => $count,
        ]);
    }
}
