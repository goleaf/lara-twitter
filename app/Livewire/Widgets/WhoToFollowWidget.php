<?php

namespace App\Livewire\Widgets;

use App\Models\User;
use App\Services\DiscoverService;
use App\Services\FollowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WhoToFollowWidget extends Component
{
    #[Computed]
    public function recommendedUsers()
    {
        if (! Auth::check()) {
            return collect();
        }

        return app(DiscoverService::class)->recommendedUsers(Auth::user(), 5);
    }

    public function toggleFollow(int $userId): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::id() === $userId, 403);

        $target = User::query()->findOrFail($userId);
        abort_if(Auth::user()->isBlockedEitherWay($target), 403);

        app(FollowService::class)->toggle(Auth::user(), $target);

        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.widgets.who-to-follow-widget');
    }
}
