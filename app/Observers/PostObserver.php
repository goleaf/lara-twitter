<?php

namespace App\Observers;

use App\Models\Hashtag;
use App\Models\Mention;
use App\Models\Post;
use App\Models\User;
use App\Notifications\FollowedUserPosted;
use App\Notifications\PostMentioned;
use App\Notifications\PostReposted;
use App\Notifications\PostReplied;
use App\Services\PostTextParser;
use Illuminate\Support\Facades\Storage;

class PostObserver
{
    public function __construct(private readonly PostTextParser $parser)
    {
    }

    public function saving(Post $post): void
    {
        // If the post starts with "@username", treat it as reply-like (limited visibility), like Twitter.
        // ".@username" is treated as a normal post.
        if ($post->reply_to_id) {
            $post->is_reply_like = false;

            return;
        }

        $body = ltrim((string) $post->body);

        if (preg_match('/^\\.@[A-Za-z0-9_]{1,30}\\b/', $body)) {
            $post->is_reply_like = false;

            return;
        }

        $post->is_reply_like = (bool) preg_match('/^@[A-Za-z0-9_]{1,30}\\b/', $body);
    }

    public function saved(Post $post): void
    {
        $existingMentionedUserIds = Mention::query()
            ->where('post_id', $post->id)
            ->pluck('mentioned_user_id')
            ->all();

        $parsed = $this->parser->parse($post->body);

        $hashtagIds = [];
        foreach ($parsed['hashtags'] as $tag) {
            $hashtagIds[] = Hashtag::query()->firstOrCreate(['tag' => $tag])->id;
        }
        $post->hashtags()->sync($hashtagIds);

        $mentionedUsers = User::query()
            ->whereIn('username', $parsed['mentions'])
            ->pluck('id', 'username');

        $mentionsToKeep = [];
        foreach ($mentionedUsers as $username => $mentionedUserId) {
            $mentionsToKeep[] = $mentionedUserId;
            Mention::query()->firstOrCreate([
                'post_id' => $post->id,
                'mentioned_user_id' => $mentionedUserId,
            ]);
        }

        Mention::query()
            ->where('post_id', $post->id)
            ->whereNotIn('mentioned_user_id', $mentionsToKeep)
            ->delete();

        $newMentionedUserIds = array_values(array_diff($mentionsToKeep, $existingMentionedUserIds));
        if (count($newMentionedUserIds)) {
            $post->loadMissing('user');

            $toNotify = User::query()
                ->whereIn('id', $newMentionedUserIds)
                ->where('id', '!=', $post->user_id)
                ->get();

            foreach ($toNotify as $user) {
                if (! $user->wantsNotification('mentions')) {
                    continue;
                }

                if (! $user->allowsNotificationFrom($post->user)) {
                    continue;
                }

                $user->notify(new PostMentioned(
                    post: $post,
                    mentionedBy: $post->user,
                ));
            }
        }
    }

    public function created(Post $post): void
    {
        if ($post->repost_of_id && ! $post->reply_to_id) {
            $original = $post->repostOf()->with('user')->first();
            if ($original && $original->user_id !== $post->user_id) {
                if ($original->user->wantsNotification('reposts')) {
                    $post->loadMissing('user');

                    if (! $original->user->allowsNotificationFrom($post->user)) {
                        return;
                    }

                    $kind = $post->body === '' ? 'retweet' : 'quote';

                    $original->user->notify(new PostReposted(
                        originalPost: $original,
                        repostPost: $post,
                        reposter: $post->user,
                        kind: $kind,
                    ));
                }
            }
        }

        if (! $post->reply_to_id) {
            if (! $post->repost_of_id) {
                $this->notifyFollowersOfNewPost($post);
            }

            return;
        }

        $original = $post->replyTo()->with('user')->first();
        if (! $original) {
            return;
        }

        if ($original->user_id === $post->user_id) {
            return;
        }

        $post->loadMissing('user');

        // Block: don't allow replies; Mute: allow, but don't notify.
        if ($post->user->isBlockedEitherWay($original->user)) {
            return;
        }

        if ($original->user->hasMuted($post->user)) {
            return;
        }

        if (! $original->user->wantsNotification('replies')) {
            return;
        }

        if (! $original->user->allowsNotificationFrom($post->user)) {
            return;
        }

        $original->user->notify(new PostReplied(
            originalPost: $original,
            replyPost: $post,
            replier: $post->user,
        ));
    }

    private function notifyFollowersOfNewPost(Post $post): void
    {
        if ($post->is_reply_like) {
            return;
        }

        $post->loadMissing('user');

        $author = $post->user;
        $followers = $author->followers()->get();

        foreach ($followers as $follower) {
            if (! $follower->wantsNotification('followed_posts')) {
                continue;
            }

            if ($author->isBlockedEitherWay($follower)) {
                continue;
            }

            if ($follower->hasMuted($author)) {
                continue;
            }

            if (! $follower->allowsNotificationFrom($author)) {
                continue;
            }

            $follower->notify(new FollowedUserPosted(
                post: $post,
                author: $author,
            ));
        }
    }

    public function deleting(Post $post): void
    {
        $post->loadMissing('images');

        foreach ($post->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        if ($post->video_path) {
            Storage::disk('public')->delete($post->video_path);
        }

        // Prevent orphaned retweets becoming empty posts if the original is deleted.
        Post::query()
            ->where('repost_of_id', $post->id)
            ->whereNull('reply_to_id')
            ->where('body', '')
            ->delete();
    }
}
