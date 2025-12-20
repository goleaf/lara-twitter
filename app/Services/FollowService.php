<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserFollowed;
use App\Services\DiscoverService;

class FollowService
{
    public function toggle(User $follower, User $followed): bool
    {
        if ($follower->is($followed)) {
            throw new \InvalidArgumentException('Cannot follow yourself.');
        }

        $changes = $follower->following()->toggle($followed->id);
        $isFollowing = count($changes['attached'] ?? []) > 0;
        $follower->flushCachedRelations();
        app(DiscoverService::class)->forgetRecommendedUsersCache($follower);

        if ($isFollowing && $followed->wantsNotification('follows') && $followed->allowsNotificationFrom($follower, 'follows')) {
            $followed->notify(new UserFollowed(follower: $follower));
        }

        return $isFollowing;
    }
}
