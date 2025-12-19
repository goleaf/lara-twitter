<?php

namespace App\Observers;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostHighEngagement;
use App\Notifications\PostLiked;

class LikeObserver
{
    private const HIGH_ENGAGEMENT_LIKES_THRESHOLD = 5;

    public function created(Like $like): void
    {
        $like->loadMissing('post.user', 'user');

        $post = $like->post;
        $postAuthor = $post->user;

        $this->notifyPostAuthorAboutLike($like, $postAuthor);
        $this->maybeNotifyHighEngagement($post);
    }

    private function notifyPostAuthorAboutLike(Like $like, User $postAuthor): void
    {
        if ($postAuthor->id === $like->user_id) {
            return;
        }

        if (! $postAuthor->allowsNotificationFrom($like->user, 'likes')) {
            return;
        }

        if (! $postAuthor->wantsNotification('likes')) {
            return;
        }

        $postAuthor->notify(new PostLiked(
            post: $like->post,
            likedBy: $like->user,
        ));
    }

    private function maybeNotifyHighEngagement(Post $post): void
    {
        if ($post->high_engagement_notified_at) {
            return;
        }

        $post->loadMissing('user');

        $author = $post->user;

        if (! $author->wantsNotification('high_engagement')) {
            return;
        }

        if ($post->created_at && $post->created_at->lt(now()->subDay())) {
            return;
        }

        $post->loadCount(['likes', 'reposts', 'replies']);

        if (($post->likes_count ?? 0) < self::HIGH_ENGAGEMENT_LIKES_THRESHOLD) {
            return;
        }

        $updated = Post::query()
            ->whereKey($post->id)
            ->whereNull('high_engagement_notified_at')
            ->update(['high_engagement_notified_at' => now()]);

        if (! $updated) {
            return;
        }

        $author->notify(new PostHighEngagement(
            post: $post,
            likesCount: (int) $post->likes_count,
            repostsCount: (int) $post->reposts_count,
            repliesCount: (int) $post->replies_count,
        ));
    }
}
