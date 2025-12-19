<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserFollowed;

class FollowService
{
    public function toggle(User $follower, User $followed): bool
    {
        if ($follower->is($followed)) {
            throw new \InvalidArgumentException('Cannot follow yourself.');
        }

        $changes = $follower->following()->toggle($followed->id);
        $isFollowing = count($changes['attached'] ?? []) > 0;

        if ($isFollowing && $followed->wantsNotification('follows') && $followed->allowsNotificationFrom($follower)) {
            $followed->notify(new UserFollowed(follower: $follower));
        }

        return $isFollowing;
    }
}
