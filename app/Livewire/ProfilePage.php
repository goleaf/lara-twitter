<?php

namespace App\Livewire;

use App\Models\Block;
use App\Models\Mute;
use App\Models\Post;
use App\Models\User;
use App\Notifications\UserFollowed;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProfilePage extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->loadCount(['followers', 'following']);

        if (Auth::check() && Auth::id() !== $this->user->id) {
            abort_if(Auth::user()->isBlockedEitherWay($this->user), 403);
            app(AnalyticsService::class)->recordUnique('profile_view', $this->user->id);
        }
    }

    public function toggleFollow(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::id() === $this->user->id, 403);
        abort_if(Auth::user()->isBlockedEitherWay($this->user), 403);

        $isFollowing = Auth::user()
            ->following()
            ->where('followed_id', $this->user->id)
            ->exists();

        if ($isFollowing) {
            Auth::user()->following()->detach($this->user->id);
        } else {
            Auth::user()->following()->attach($this->user->id);

            if ($this->user->wantsNotification('follows') && $this->user->allowsNotificationFrom(Auth::user())) {
                $this->user->notify(new UserFollowed(follower: Auth::user()));
            }
        }

        $this->user->loadCount(['followers', 'following']);
    }

    public function toggleMute(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::id() === $this->user->id, 403);
        abort_if(Auth::user()->isBlockedEitherWay($this->user), 403);

        $existing = Mute::query()
            ->where('muter_id', Auth::id())
            ->where('muted_id', $this->user->id)
            ->exists();

        if ($existing) {
            Mute::query()
                ->where('muter_id', Auth::id())
                ->where('muted_id', $this->user->id)
                ->delete();
        } else {
            Mute::query()->create([
                'muter_id' => Auth::id(),
                'muted_id' => $this->user->id,
            ]);
        }
    }

    public function toggleBlock(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::id() === $this->user->id, 403);

        $existing = Block::query()
            ->where('blocker_id', Auth::id())
            ->where('blocked_id', $this->user->id)
            ->exists();

        if ($existing) {
            Block::query()
                ->where('blocker_id', Auth::id())
                ->where('blocked_id', $this->user->id)
                ->delete();
        } else {
            Block::query()->create([
                'blocker_id' => Auth::id(),
                'blocked_id' => $this->user->id,
            ]);

            // Remove follow relationships in both directions.
            Auth::user()->following()->detach($this->user->id);
            Auth::user()->followers()->detach($this->user->id);
        }

        // If blocked, also remove mute entry for tidiness.
        Mute::query()
            ->where('muter_id', Auth::id())
            ->where('muted_id', $this->user->id)
            ->delete();
    }

    public function getPostsProperty()
    {
        return Post::query()
            ->where('user_id', $this->user->id)
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->latest()
            ->paginate(15);
    }

    public function getIsFollowingProperty(): bool
    {
        if (! Auth::check() || Auth::id() === $this->user->id) {
            return false;
        }

        if (Auth::user()->isBlockedEitherWay($this->user)) {
            return false;
        }

        return Auth::user()
            ->following()
            ->where('followed_id', $this->user->id)
            ->exists();
    }

    public function getIsMutedProperty(): bool
    {
        if (! Auth::check() || Auth::id() === $this->user->id) {
            return false;
        }

        if (Auth::user()->isBlockedEitherWay($this->user)) {
            return false;
        }

        return Auth::user()->hasMuted($this->user);
    }

    public function getHasBlockedProperty(): bool
    {
        if (! Auth::check() || Auth::id() === $this->user->id) {
            return false;
        }

        return Auth::user()->hasBlocked($this->user);
    }

    public function render()
    {
        return view('livewire.profile-page')->layout('layouts.app');
    }
}
