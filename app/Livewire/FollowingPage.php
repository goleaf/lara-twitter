<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FollowingPage extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->loadCount(['followers', 'following']);

        if (Auth::check() && Auth::id() !== $this->user->id) {
            abort_if(Auth::user()->isBlockedEitherWay($this->user), 403);
        }
    }

    public function toggleFollow(int $targetUserId): void
    {
        abort_unless(Auth::check(), 403);

        if (Auth::id() === $targetUserId) {
            return;
        }

        $target = User::query()->findOrFail($targetUserId);
        abort_if(Auth::user()->isBlockedEitherWay($target), 403);

        $isFollowing = Auth::user()
            ->following()
            ->where('followed_id', $targetUserId)
            ->exists();

        if ($isFollowing) {
            Auth::user()->following()->detach($targetUserId);
        } else {
            Auth::user()->following()->attach($targetUserId);
        }
    }

    public function isFollowing(int $targetUserId): bool
    {
        if (! Auth::check() || Auth::id() === $targetUserId) {
            return false;
        }

        return Auth::user()
            ->following()
            ->where('followed_id', $targetUserId)
            ->exists();
    }

    public function getFollowingProperty()
    {
        return $this->user
            ->following()
            ->getQuery()
            ->orderBy('follows.created_at', 'desc')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.following-page')->layout('layouts.app');
    }
}

