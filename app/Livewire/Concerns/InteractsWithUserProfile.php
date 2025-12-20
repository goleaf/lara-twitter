<?php

namespace App\Livewire\Concerns;

use App\Models\Block;
use App\Models\Mute;
use App\Models\UserList;
use App\Services\FollowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

trait InteractsWithUserProfile
{
    public function toggleFollow(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::id() === $this->user->id, 403);
        abort_if(Auth::user()->isBlockedEitherWay($this->user), 403);

        app(FollowService::class)->toggle(Auth::user(), $this->user);

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

            Auth::user()->flushCachedRelations();

            return;
        }

        Mute::query()->create([
            'muter_id' => Auth::id(),
            'muted_id' => $this->user->id,
        ]);

        Auth::user()->flushCachedRelations();
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

        Auth::user()->flushCachedRelations();
        $this->user->flushCachedRelations();

        $this->user->loadCount(['followers', 'following']);
    }

    #[Computed]
    public function isFollowing(): bool
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

    #[Computed]
    public function isMuted(): bool
    {
        if (! Auth::check() || Auth::id() === $this->user->id) {
            return false;
        }

        if (Auth::user()->isBlockedEitherWay($this->user)) {
            return false;
        }

        return Auth::user()->hasMuted($this->user);
    }

    #[Computed]
    public function hasBlocked(): bool
    {
        if (! Auth::check() || Auth::id() === $this->user->id) {
            return false;
        }

        return Auth::user()->hasBlocked($this->user);
    }

    #[Computed]
    public function listsCount(): int
    {
        return UserList::query()
            ->where('is_private', false)
            ->whereHas('members', fn ($q) => $q->where('users.id', $this->user->id))
            ->count();
    }
}
