<?php

namespace App\Observers;

use App\Models\Like;
use App\Notifications\PostLiked;

class LikeObserver
{
    public function created(Like $like): void
    {
        $like->loadMissing('post.user', 'user');

        $postAuthor = $like->post->user;

        if ($postAuthor->id === $like->user_id) {
            return;
        }

        if (! $postAuthor->wantsNotification('likes')) {
            return;
        }

        if (! $postAuthor->allowsNotificationFrom($like->user, 'likes')) {
            return;
        }

        $postAuthor->notify(new PostLiked(
            post: $like->post,
            likedBy: $like->user,
        ));
    }
}
