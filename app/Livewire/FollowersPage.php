<?php

namespace App\Livewire;

use App\Models\Follow;
use App\Models\User;
use App\Services\FollowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FollowersPage extends Component
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

    public function removeFollower(int $followerId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->user->id, 403);

        Follow::query()
            ->where('followed_id', $this->user->id)
            ->where('follower_id', $followerId)
            ->delete();

        $this->user->loadCount(['followers', 'following']);
        $this->resetPage();
    }

    public function toggleFollow(int $targetUserId): void
    {
        abort_unless(Auth::check(), 403);

        if (Auth::id() === $targetUserId) {
            return;
        }

        $target = User::query()->findOrFail($targetUserId);
        abort_if(Auth::user()->isBlockedEitherWay($target), 403);

        app(FollowService::class)->toggle(Auth::user(), $target);
    }

    public function isFollowing(int $targetUserId): bool
    {
        if (! Auth::check() || Auth::id() === $targetUserId) {
            return false;
        }

        return in_array($targetUserId, $this->followingIds, true);
    }

    public function getFollowingIdsProperty(): array
    {
        if (! Auth::check()) {
            return [];
        }

        return Auth::user()
            ->followingIds()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function getFollowersProperty()
    {
        return $this->user
            ->followers()
            ->select(['users.id', 'users.name', 'users.username', 'users.avatar_path', 'users.is_verified'])
            ->orderBy('follows.created_at', 'desc')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.followers-page')->layout('layouts.app');
    }
}
