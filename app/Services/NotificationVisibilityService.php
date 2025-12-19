<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class NotificationVisibilityService
{
    public function visibleUnreadCount(User $viewer, int $limit = 200): int
    {
        $items = $viewer
            ->unreadNotifications()
            ->latest()
            ->limit($limit)
            ->get();

        return $this->filter($viewer, $items)->count();
    }

    /**
     * @param  Collection<int, \Illuminate\Notifications\DatabaseNotification>  $items
     * @return Collection<int, \Illuminate\Notifications\DatabaseNotification>
     */
    public function filter(User $viewer, Collection $items): Collection
    {
        if ($items->isEmpty()) {
            return $items;
        }

        $blockedIds = $viewer->blocksInitiated()->pluck('blocked_id');
        $blockedByIds = $viewer->blocksReceived()->pluck('blocker_id');
        $blockedSet = array_fill_keys($blockedIds->merge($blockedByIds)->unique()->values()->all(), true);

        $mutedSet = array_fill_keys($viewer->mutesInitiated()->pluck('muted_id')->all(), true);

        $terms = $viewer
            ->mutedTerms()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where('mute_notifications', true)
            ->latest()
            ->limit(50)
            ->get();

        $followingSet = [];
        if ($terms->contains(fn ($t) => (bool) $t->only_non_followed)) {
            $followingIds = $viewer->following()->pluck('users.id')->push($viewer->id)->all();
            $followingSet = array_fill_keys($followingIds, true);
        }

        $matcher = app(MutedTermMatcher::class);

        return $items
            ->filter(function ($notification) use ($blockedSet, $mutedSet, $terms, $followingSet, $matcher) {
                $data = $notification->data ?? [];
                $type = (string) Arr::get($data, 'type', '');

                $actorUserId = Arr::get($data, 'actor_user_id');
                $actorUserId = is_numeric($actorUserId) ? (int) $actorUserId : null;

                if ($actorUserId && isset($blockedSet[$actorUserId])) {
                    return false;
                }

                if ($type !== 'message_received' && $actorUserId && isset($mutedSet[$actorUserId])) {
                    return false;
                }

                if ($type === 'message_received') {
                    return true;
                }

                if ($terms->isEmpty()) {
                    return true;
                }

                $excerpt = (string) Arr::get($data, 'excerpt', '');
                if ($excerpt === '') {
                    return true;
                }

                foreach ($terms as $term) {
                    if ($term->only_non_followed && $actorUserId && isset($followingSet[$actorUserId])) {
                        continue;
                    }

                    if ($matcher->matches($excerpt, $term)) {
                        return false;
                    }
                }

                return true;
            })
            ->values();
    }
}

