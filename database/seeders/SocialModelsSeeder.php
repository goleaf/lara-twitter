<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Hashtag;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Moment;
use App\Models\MutedTerm;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostLinkPreview;
use App\Models\PostPoll;
use App\Models\PostPollOption;
use App\Models\Report;
use App\Models\Space;
use App\Models\SpaceSpeakerRequest;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SocialModelsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $modelCount = $this->seedCount('model_count', 1000);
        $relationCount = $this->seedCount('relation_count', 1000);

        if ($modelCount === 0 && $relationCount === 0) {
            return;
        }

        $now = now();

        $users = $this->seedUsers($modelCount);
        $userIds = $users->pluck('id')->all();

        $hashtags = $this->seedHashtags($modelCount);
        $hashtagIds = $hashtags->pluck('id')->all();

        $posts = $this->seedPosts($modelCount, $userIds);
        $postIds = $posts->pluck('id')->all();

        $postRelations = $this->applyPostRelations($postIds, $relationCount);
        $this->seedPostVariations($postIds, $postRelations['replyIds'], $postRelations['repostIds'], $modelCount);
        $this->seedPinnedPostsForUsers($users, $posts, $relationCount);

        $userLists = $this->seedUserLists($modelCount, $userIds);
        $userListIds = $userLists->pluck('id')->all();
        [$emptyListId, $subscriberOnlyListId] = $this->pickSpecialLists($userListIds);

        $conversations = $this->seedConversations($modelCount, $userIds);
        $conversationParticipants = $this->seedConversationParticipants($relationCount, $conversations, $userIds, $now);
        $messages = $this->seedMessages($modelCount, $conversations, $conversationParticipants, $now);
        $messageIds = $messages->pluck('id')->all();

        $spaces = $this->seedSpaces($modelCount, $userIds);
        $spaceIds = $spaces->pluck('id')->all();
        $this->seedPinnedPostsForSpaces($spaceIds, $postIds, $relationCount);

        $moments = $this->seedMoments($modelCount, $userIds);
        $momentIds = $moments->pluck('id')->all();

        $this->seedFollows($relationCount, $userIds, $now);
        $blockPairs = $this->seedBlocks($relationCount, $userIds, $now);
        $this->seedMutes($relationCount, $userIds, $now, $blockPairs);
        $this->seedLikes($relationCount, $userIds, $postIds, $now);
        $this->seedBookmarks($relationCount, $userIds, $postIds, $now);
        $this->seedMentions($relationCount, $postIds, $userIds, $now);
        $this->seedHashtagPosts($relationCount, $hashtagIds, $postIds);
        $this->seedPostImages($relationCount, $postIds);
        $pollIds = $this->seedPostPolls($relationCount, $postIds);
        $pollOptionMap = $this->seedPostPollOptions($pollIds, $relationCount);
        $this->seedPostPollVotes($relationCount, $pollOptionMap, $userIds, $now);
        $this->seedPostLinkPreviews($relationCount, $postIds);
        $this->seedMessageAttachments($relationCount, $messageIds);
        $this->seedMessageReactions($relationCount, $messageIds, $userIds, $now);
        $this->seedUserListMemberships($relationCount, $userListIds, $userIds, $now, [$emptyListId, $subscriberOnlyListId]);
        $this->seedSubscriberOnlyListSubscriptions($subscriberOnlyListId, $userIds, $now, $relationCount);
        $this->seedUserListSubscriptions($relationCount, $userListIds, $userIds, $now, [$emptyListId]);
        $this->seedMutedTerms($relationCount, $userIds);
        $this->seedSpaceParticipants($relationCount, $spaces, $userIds, $now);
        $this->seedSpaceSpeakerRequests($relationCount, $spaceIds, $userIds, $now);
        $this->seedSpaceReactions($relationCount, $spaceIds, $userIds, $now);
        $this->seedMomentItems($relationCount, $momentIds, $postIds, $now);
        $this->seedReports($relationCount, $userIds, $postIds, $messageIds, $spaceIds, $hashtagIds, $userListIds, $now);
    }

    private function seedCount(string $key, int $default): int
    {
        return max(0, (int) config("seeding.{$key}", $default));
    }

    private function seedUsers(int $modelCount): Collection
    {
        if ($modelCount <= 0) {
            return collect();
        }

        $admin = $this->seedAdminUser();

        $remaining = max($modelCount - 1, 0);
        if ($remaining <= 0) {
            return collect([$admin]);
        }

        $users = User::factory()
            ->count($remaining)
            ->state(fn () => [
                'email_verified_at' => fake()->boolean(85) ? now() : null,
                'is_verified' => fake()->boolean(20),
                'is_premium' => fake()->boolean(15),
                'dm_policy' => fake()->randomElement(User::dmPolicies()),
                'dm_allow_requests' => fake()->boolean(80),
                'dm_read_receipts' => fake()->boolean(75),
                'avatar_path' => fake()->optional(0.35)->passthrough('seed-avatars/'.fake()->uuid().'.jpg'),
                'header_path' => fake()->optional(0.25)->passthrough('seed-headers/'.fake()->uuid().'.jpg'),
                'location' => fake()->optional(0.35)->city(),
                'website' => fake()->optional(0.35)->url(),
            ])
            ->create();

        $this->ensureUserCoverage($users);

        return $users->prepend($admin);
    }

    private function seedAdminUser(): User
    {
        $email = 'admin@admin.com';
        $baseUsername = 'admin';

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $candidate = $baseUsername;
            $suffix = 1;

            while (User::query()->where('username', $candidate)->exists()) {
                $suffix++;
                $candidate = $baseUsername . $suffix;
            }

            return User::factory()->create([
                'name' => 'Admin',
                'username' => $candidate,
                'email' => $email,
                'password' => Hash::make('admin'),
                'is_admin' => true,
                'is_verified' => true,
                'is_premium' => true,
                'dm_policy' => User::DM_EVERYONE,
                'dm_allow_requests' => true,
                'dm_read_receipts' => true,
                'email_verified_at' => now(),
                'bio' => 'Admin account (Filament access).',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make('admin'),
            'is_admin' => true,
            'is_verified' => true,
            'is_premium' => true,
        ])->save();

        return $user;
    }

    private function ensureUserCoverage(Collection $users): void
    {
        $users = $users->values();
        if ($users->isEmpty()) {
            return;
        }

        $index = 0;
        foreach (User::dmPolicies() as $policy) {
            if (! isset($users[$index])) {
                break;
            }

            User::query()->whereKey($users[$index]->id)->update([
                'dm_policy' => $policy,
                'dm_allow_requests' => $policy !== User::DM_NONE,
                'dm_read_receipts' => true,
            ]);

            $index++;
        }

        if (isset($users[$index])) {
            User::query()->whereKey($users[$index]->id)->update(['dm_allow_requests' => false]);
            $index++;
        }

        if (isset($users[$index])) {
            User::query()->whereKey($users[$index]->id)->update(['dm_read_receipts' => false]);
            $index++;
        }

        if (isset($users[$index])) {
            User::query()->whereKey($users[$index]->id)->update(['is_premium' => true]);
            $index++;
        }

        if (isset($users[$index])) {
            User::query()->whereKey($users[$index]->id)->update([
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);
            $index++;
        }

        if (isset($users[$index])) {
            User::query()->whereKey($users[$index]->id)->update([
                'is_verified' => false,
                'email_verified_at' => null,
            ]);
            $index++;
        }

        if (isset($users[$index])) {
            User::query()->whereKey($users[$index]->id)->update([
                'avatar_path' => 'seed-avatars/'.fake()->uuid().'.jpg',
                'header_path' => 'seed-headers/'.fake()->uuid().'.jpg',
                'bio' => fake()->text(120),
                'location' => fake()->city(),
                'website' => fake()->url(),
            ]);
        }
    }

    private function seedHashtags(int $modelCount): Collection
    {
        if ($modelCount <= 0) {
            return collect();
        }

        return Hashtag::factory($modelCount)->create();
    }

    private function seedPosts(int $modelCount, array $userIds): Collection
    {
        if ($modelCount <= 0 || $userIds === []) {
            return collect();
        }

        return Post::factory()
            ->count($modelCount)
            ->state(fn () => [
                'user_id' => $userIds[array_rand($userIds)],
                'reply_policy' => fake()->randomElement(Post::replyPolicies()),
            ])
            ->create();
    }

    private function applyPostRelations(array $postIds, int $relationCount): array
    {
        $postCount = count($postIds);
        if ($postCount < 2) {
            return ['replyIds' => [], 'repostIds' => []];
        }

        $replyCount = min($postCount, max(1, intdiv($postCount, 5)));
        $repostCount = min($postCount - $replyCount, max(1, intdiv($postCount, 6)));
        if ($relationCount > 0) {
            $replyCount = min($replyCount, $relationCount);
            $repostCount = min($repostCount, max(0, $relationCount - $replyCount));
        }

        if ($postCount > 1) {
            $maxRelated = $postCount - 1;
            if ($replyCount + $repostCount > $maxRelated) {
                $repostCount = max(0, $maxRelated - $replyCount);
            }
        }

        $shuffled = $postIds;
        shuffle($shuffled);
        $replyTargets = array_slice($shuffled, 0, $replyCount);
        $repostTargets = array_slice($shuffled, $replyCount, $repostCount);

        foreach ($replyTargets as $postId) {
            $replyTo = $this->randomOtherId($postIds, $postId);
            if ($replyTo) {
                Post::query()->withoutGlobalScope('published')->whereKey($postId)->update([
                    'reply_to_id' => $replyTo,
                    'repost_of_id' => null,
                    'is_reply_like' => false,
                ]);
            }
        }

        foreach ($repostTargets as $postId) {
            $repostOf = $this->randomOtherId($postIds, $postId);
            if ($repostOf) {
                Post::query()->withoutGlobalScope('published')->whereKey($postId)->update([
                    'repost_of_id' => $repostOf,
                    'reply_to_id' => null,
                    'is_reply_like' => false,
                    'body' => fake()->boolean(50) ? '' : fake()->sentence(fake()->numberBetween(6, 16)),
                ]);
            }
        }

        return [
            'replyIds' => $replyTargets,
            'repostIds' => $repostTargets,
        ];
    }

    private function seedPinnedPostsForUsers(array $userIds, array $postIds, int $relationCount): void
    {
        if ($relationCount <= 0 || $userIds === [] || $postIds === []) {
            return;
        }

        $targets = $this->randomSample($userIds, min($relationCount, count($userIds)));
        foreach ($targets as $userId) {
            User::query()->whereKey($userId)->update([
                'pinned_post_id' => $this->randomId($postIds),
            ]);
        }
    }

    private function seedUserLists(int $modelCount, array $userIds): Collection
    {
        if ($modelCount <= 0 || $userIds === []) {
            return collect();
        }

        return UserList::factory()
            ->count($modelCount)
            ->state(fn () => ['owner_id' => $userIds[array_rand($userIds)]])
            ->create();
    }

    private function seedConversations(int $modelCount, array $userIds): Collection
    {
        if ($modelCount <= 0 || $userIds === []) {
            return collect();
        }

        return Conversation::factory()
            ->count($modelCount)
            ->state(fn () => ['created_by_user_id' => $userIds[array_rand($userIds)]])
            ->create();
    }

    private function seedMessages(int $modelCount, array $conversationIds, array $userIds): Collection
    {
        if ($modelCount <= 0 || $conversationIds === [] || $userIds === []) {
            return collect();
        }

        return Message::factory()
            ->count($modelCount)
            ->state(fn () => [
                'conversation_id' => $conversationIds[array_rand($conversationIds)],
                'user_id' => $userIds[array_rand($userIds)],
            ])
            ->create();
    }

    private function seedSpaces(int $modelCount, array $userIds): Collection
    {
        if ($modelCount <= 0 || $userIds === []) {
            return collect();
        }

        return Space::factory()
            ->count($modelCount)
            ->state(fn () => ['host_user_id' => $userIds[array_rand($userIds)]])
            ->create();
    }

    private function seedPinnedPostsForSpaces(array $spaceIds, array $postIds, int $relationCount): void
    {
        if ($relationCount <= 0 || $spaceIds === [] || $postIds === []) {
            return;
        }

        $targets = $this->randomSample($spaceIds, min($relationCount, count($spaceIds)));
        foreach ($targets as $spaceId) {
            Space::query()->whereKey($spaceId)->update([
                'pinned_post_id' => $this->randomId($postIds),
            ]);
        }
    }

    private function seedMoments(int $modelCount, array $userIds): Collection
    {
        if ($modelCount <= 0 || $userIds === []) {
            return collect();
        }

        return Moment::factory()
            ->count($modelCount)
            ->state(fn () => ['owner_id' => $userIds[array_rand($userIds)]])
            ->create();
    }

    private function seedFollows(int $relationCount, array $userIds, $now): void
    {
        $pairs = $this->uniquePairs($userIds, $userIds, $relationCount, true);
        $rows = array_map(fn (array $pair) => [
            'follower_id' => $pair[0],
            'followed_id' => $pair[1],
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);
        $this->insertRows('follows', $rows);
    }

    private function seedBlocks(int $relationCount, array $userIds, $now): void
    {
        $pairs = $this->uniquePairs($userIds, $userIds, $relationCount, true);
        $rows = array_map(fn (array $pair) => [
            'blocker_id' => $pair[0],
            'blocked_id' => $pair[1],
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);
        $this->insertRows('blocks', $rows);
    }

    private function seedMutes(int $relationCount, array $userIds, $now): void
    {
        $pairs = $this->uniquePairs($userIds, $userIds, $relationCount, true);
        $rows = array_map(fn (array $pair) => [
            'muter_id' => $pair[0],
            'muted_id' => $pair[1],
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);
        $this->insertRows('mutes', $rows);
    }

    private function seedLikes(int $relationCount, array $userIds, array $postIds, $now): void
    {
        $pairs = $this->uniquePairs($userIds, $postIds, $relationCount);
        $rows = array_map(fn (array $pair) => [
            'user_id' => $pair[0],
            'post_id' => $pair[1],
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);
        $this->insertRows('likes', $rows);
    }

    private function seedBookmarks(int $relationCount, array $userIds, array $postIds, $now): void
    {
        $pairs = $this->uniquePairs($userIds, $postIds, $relationCount);
        $rows = array_map(fn (array $pair) => [
            'user_id' => $pair[0],
            'post_id' => $pair[1],
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);
        $this->insertRows('bookmarks', $rows);
    }

    private function seedMentions(int $relationCount, array $postIds, array $userIds, $now): void
    {
        $pairs = $this->uniquePairs($postIds, $userIds, $relationCount);
        $rows = array_map(fn (array $pair) => [
            'post_id' => $pair[0],
            'mentioned_user_id' => $pair[1],
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);
        $this->insertRows('mentions', $rows);
    }

    private function seedHashtagPosts(int $relationCount, array $hashtagIds, array $postIds): void
    {
        $pairs = $this->uniquePairs($hashtagIds, $postIds, $relationCount);
        $rows = array_map(fn (array $pair) => [
            'hashtag_id' => $pair[0],
            'post_id' => $pair[1],
        ], $pairs);
        $this->insertRows('hashtag_post', $rows);
    }

    private function seedPostImages(int $relationCount, array $postIds): void
    {
        if ($relationCount <= 0 || $postIds === []) {
            return;
        }

        PostImage::factory()
            ->count($relationCount)
            ->state(fn () => ['post_id' => $postIds[array_rand($postIds)]])
            ->create();
    }

    private function seedPostPolls(int $relationCount, array $postIds): array
    {
        if ($relationCount <= 0 || $postIds === []) {
            return [];
        }

        $targets = $this->randomSample($postIds, min($relationCount, count($postIds)));
        $pollIds = [];
        foreach ($targets as $postId) {
            $poll = PostPoll::factory()->state(['post_id' => $postId])->create();
            $pollIds[] = $poll->id;
        }

        return $pollIds;
    }

    private function seedPostPollOptions(array $pollIds, int $relationCount): array
    {
        if ($pollIds === [] || $relationCount <= 0) {
            return [];
        }

        $pollOptionMap = [];

        $minOptionsPerPoll = 2;
        if ($relationCount < $minOptionsPerPoll) {
            $pollId = $this->randomId($pollIds);
            if (! $pollId) {
                return [];
            }

            $optionIds = [];
            for ($i = 0; $i < $relationCount; $i++) {
                $option = PostPollOption::factory()->state([
                    'post_poll_id' => $pollId,
                    'sort_order' => $i,
                ])->create();
                $optionIds[] = $option->id;
            }

            $pollOptionMap[$pollId] = $optionIds;

            return $pollOptionMap;
        }

        $pollsWithOptions = min(count($pollIds), intdiv($relationCount, $minOptionsPerPoll));
        if ($pollsWithOptions <= 0) {
            return [];
        }

        $targetPollIds = $this->randomSample($pollIds, $pollsWithOptions);
        foreach ($targetPollIds as $pollId) {
            $optionIds = [];
            for ($i = 0; $i < $minOptionsPerPoll; $i++) {
                $option = PostPollOption::factory()->state([
                    'post_poll_id' => $pollId,
                    'sort_order' => $i,
                ])->create();
                $optionIds[] = $option->id;
            }

            $pollOptionMap[$pollId] = $optionIds;
        }

        $remaining = $relationCount - ($pollsWithOptions * $minOptionsPerPoll);
        $index = 0;
        while ($remaining > 0) {
            $pollId = $targetPollIds[$index % $pollsWithOptions];
            $sortOrder = count($pollOptionMap[$pollId]);
            $option = PostPollOption::factory()->state([
                'post_poll_id' => $pollId,
                'sort_order' => $sortOrder,
            ])->create();
            $pollOptionMap[$pollId][] = $option->id;
            $remaining--;
            $index++;
        }

        return $pollOptionMap;
    }

    private function seedPostPollVotes(int $relationCount, array $pollOptionMap, array $userIds, $now): void
    {
        $pollIds = array_keys($pollOptionMap);
        if ($relationCount <= 0 || $pollIds === [] || $userIds === []) {
            return;
        }

        $pairs = $this->uniquePairs($pollIds, $userIds, $relationCount);
        $rows = [];
        foreach ($pairs as $pair) {
            [$pollId, $userId] = $pair;
            $optionIds = $pollOptionMap[$pollId] ?? [];
            if ($optionIds === []) {
                continue;
            }
            $rows[] = [
                'post_poll_id' => $pollId,
                'post_poll_option_id' => $this->randomId($optionIds),
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->insertRows('post_poll_votes', $rows);
    }

    private function seedPostLinkPreviews(int $relationCount, array $postIds): void
    {
        if ($relationCount <= 0 || $postIds === []) {
            return;
        }

        $targets = $this->randomSample($postIds, min($relationCount, count($postIds)));
        foreach ($targets as $postId) {
            PostLinkPreview::factory()->state(['post_id' => $postId])->create();
        }
    }

    private function seedConversationParticipants(int $relationCount, Collection $conversations, array $userIds, $now): void
    {
        if ($conversations->isEmpty() || $userIds === []) {
            return;
        }

        $rows = [];
        $used = [];

        foreach ($conversations as $conversation) {
            $key = $conversation->id.'-'.$conversation->created_by_user_id;
            $used[$key] = true;
            $rows[] = [
                'conversation_id' => $conversation->id,
                'user_id' => $conversation->created_by_user_id,
                'last_read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $target = max($relationCount, count($rows));
        if ($target > count($rows)) {
            $pairs = $this->uniquePairs(
                $conversations->pluck('id')->all(),
                $userIds,
                $target - count($rows),
                false,
                $used
            );

            foreach ($pairs as $pair) {
                $rows[] = [
                    'conversation_id' => $pair[0],
                    'user_id' => $pair[1],
                    'last_read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->insertRows('conversation_participants', $rows);
    }

    private function seedMessageAttachments(int $relationCount, array $messageIds): void
    {
        if ($relationCount <= 0 || $messageIds === []) {
            return;
        }

        MessageAttachment::factory()
            ->count($relationCount)
            ->state(fn () => ['message_id' => $messageIds[array_rand($messageIds)]])
            ->create();
    }

    private function seedMessageReactions(int $relationCount, array $messageIds, array $userIds, $now): void
    {
        if ($relationCount <= 0 || $messageIds === [] || $userIds === []) {
            return;
        }

        $emojis = ['👍', '❤️', '😂', '🔥', '👏', '😮', '😢', '🎉'];
        $triples = $this->uniqueTriples($messageIds, $userIds, $emojis, $relationCount);
        $rows = array_map(fn (array $triple) => [
            'message_id' => $triple[0],
            'user_id' => $triple[1],
            'emoji' => $triple[2],
            'created_at' => $now,
            'updated_at' => $now,
        ], $triples);

        $this->insertRows('message_reactions', $rows);
    }

    private function seedUserListMemberships(int $relationCount, array $userListIds, array $userIds, $now): void
    {
        $pairs = $this->uniquePairs($userListIds, $userIds, $relationCount);
        $rows = array_map(fn (array $pair) => [
            'user_list_id' => $pair[0],
            'user_id' => $pair[1],
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);
        $this->insertRows('user_list_user', $rows);
    }

    private function seedUserListSubscriptions(int $relationCount, array $userListIds, array $userIds, $now): void
    {
        $pairs = $this->uniquePairs($userListIds, $userIds, $relationCount);
        $rows = array_map(fn (array $pair) => [
            'user_list_id' => $pair[0],
            'user_id' => $pair[1],
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);
        $this->insertRows('user_list_subscriptions', $rows);
    }

    private function seedMutedTerms(int $relationCount, array $userIds): void
    {
        if ($relationCount <= 0 || $userIds === []) {
            return;
        }

        MutedTerm::factory()
            ->count($relationCount)
            ->state(fn () => ['user_id' => $userIds[array_rand($userIds)]])
            ->create();
    }

    private function seedSpaceParticipants(int $relationCount, Collection $spaces, array $userIds, $now): void
    {
        if ($spaces->isEmpty() || $userIds === []) {
            return;
        }

        $rows = [];
        $used = [];

        foreach ($spaces as $space) {
            $key = $space->id.'-'.$space->host_user_id;
            $used[$key] = true;
            $rows[] = [
                'space_id' => $space->id,
                'user_id' => $space->host_user_id,
                'role' => 'host',
                'joined_at' => null,
                'left_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $target = max($relationCount, count($rows));
        if ($target > count($rows)) {
            $pairs = $this->uniquePairs(
                $spaces->pluck('id')->all(),
                $userIds,
                $target - count($rows),
                false,
                $used
            );

            $roles = ['listener', 'speaker', 'cohost'];
            foreach ($pairs as $pair) {
                $rows[] = [
                    'space_id' => $pair[0],
                    'user_id' => $pair[1],
                    'role' => $roles[array_rand($roles)],
                    'joined_at' => null,
                    'left_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->insertRows('space_participants', $rows);
    }

    private function seedSpaceSpeakerRequests(int $relationCount, array $spaceIds, array $userIds, $now): void
    {
        if ($relationCount <= 0 || $spaceIds === [] || $userIds === []) {
            return;
        }

        $pairs = $this->uniquePairs($spaceIds, $userIds, $relationCount);
        $rows = array_map(fn (array $pair) => [
            'space_id' => $pair[0],
            'user_id' => $pair[1],
            'status' => SpaceSpeakerRequest::STATUS_PENDING,
            'decided_by' => null,
            'decided_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $pairs);

        $this->insertRows('space_speaker_requests', $rows);
    }

    private function seedSpaceReactions(int $relationCount, array $spaceIds, array $userIds, $now): void
    {
        if ($relationCount <= 0 || $spaceIds === [] || $userIds === []) {
            return;
        }

        $emojis = ['👍', '❤️', '😂', '🔥', '👏', '😮', '😢', '🎉'];
        $rows = [];
        for ($i = 0; $i < $relationCount; $i++) {
            $rows[] = [
                'space_id' => $this->randomId($spaceIds),
                'user_id' => $this->randomId($userIds),
                'emoji' => $emojis[array_rand($emojis)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->insertRows('space_reactions', $rows);
    }

    private function seedMomentItems(int $relationCount, array $momentIds, array $postIds, $now): void
    {
        if ($momentIds === [] || $postIds === []) {
            return;
        }

        $rows = [];
        $used = [];

        foreach ($momentIds as $momentId) {
            $postId = $this->randomId($postIds);
            if (! $postId) {
                continue;
            }
            $key = $momentId.'-'.$postId;
            $used[$key] = true;
            $rows[] = [
                'moment_id' => $momentId,
                'post_id' => $postId,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $target = max($relationCount, count($rows));
        if ($target > count($rows)) {
            $pairs = $this->uniquePairs($momentIds, $postIds, $target - count($rows), false, $used);
            foreach ($pairs as $index => $pair) {
                $rows[] = [
                    'moment_id' => $pair[0],
                    'post_id' => $pair[1],
                    'sort_order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->insertRows('moment_items', $rows);
    }

    private function seedReports(
        int $relationCount,
        array $userIds,
        array $postIds,
        array $messageIds,
        array $spaceIds,
        array $hashtagIds,
        array $userListIds,
        $now
    ): void {
        if ($relationCount <= 0 || $userIds === []) {
            return;
        }

        $reasons = Report::reasons();
        $targets = [
            ['type' => Post::class, 'ids' => $postIds, 'disallowSame' => false],
            ['type' => Message::class, 'ids' => $messageIds, 'disallowSame' => false],
            ['type' => Space::class, 'ids' => $spaceIds, 'disallowSame' => false],
            ['type' => Hashtag::class, 'ids' => $hashtagIds, 'disallowSame' => false],
            ['type' => UserList::class, 'ids' => $userListIds, 'disallowSame' => false],
            ['type' => User::class, 'ids' => $userIds, 'disallowSame' => true],
        ];

        $targets = array_values(array_filter($targets, fn (array $target) => $target['ids'] !== []));
        if ($targets === []) {
            return;
        }

        $typeCount = count($targets);
        $base = intdiv($relationCount, $typeCount);
        $remainder = $relationCount % $typeCount;

        $rows = [];
        foreach ($targets as $index => $target) {
            $count = $base + ($index < $remainder ? 1 : 0);
            if ($count <= 0) {
                continue;
            }

            $rows = array_merge(
                $rows,
                $this->buildReportRows(
                    $target['type'],
                    $target['ids'],
                    $userIds,
                    $count,
                    $reasons,
                    $now,
                    $target['disallowSame']
                )
            );
        }

        $this->insertRows('reports', $rows);
    }

    private function buildReportRows(
        string $reportableType,
        array $reportableIds,
        array $reporterIds,
        int $count,
        array $reasons,
        $now,
        bool $disallowSameReporter = false
    ): array {
        if ($count <= 0 || $reportableIds === [] || $reporterIds === []) {
            return [];
        }

        $pairs = $this->uniquePairs($reporterIds, $reportableIds, $count, $disallowSameReporter);
        $rows = [];
        foreach ($pairs as $pair) {
            $rows[] = [
                'reporter_id' => $pair[0],
                'reportable_type' => $reportableType,
                'reportable_id' => $pair[1],
                'reason' => $reasons[array_rand($reasons)],
                'details' => fake()->optional()->text(140),
                'status' => Report::STATUS_OPEN,
                'admin_notes' => null,
                'resolved_by' => null,
                'resolved_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    private function uniquePairs(
        array $leftIds,
        array $rightIds,
        int $count,
        bool $disallowSame = false,
        array $used = []
    ): array {
        $leftCount = count($leftIds);
        $rightCount = count($rightIds);
        if ($count <= 0 || $leftCount === 0 || $rightCount === 0) {
            return [];
        }

        $max = $leftCount * $rightCount;
        if ($disallowSame && $leftIds === $rightIds) {
            $max = $leftCount * max(0, $rightCount - 1);
        }

        $target = min($count, max(0, $max - count($used)));
        if ($target <= 0) {
            return [];
        }

        $pairs = [];
        $attempts = 0;
        $attemptLimit = $target * 20;

        while (count($pairs) < $target && $attempts < $attemptLimit) {
            $left = $leftIds[array_rand($leftIds)];
            $right = $rightIds[array_rand($rightIds)];
            if ($disallowSame && $left === $right) {
                $attempts++;
                continue;
            }

            $key = $left.'-'.$right;
            if (isset($used[$key])) {
                $attempts++;
                continue;
            }

            $used[$key] = true;
            $pairs[] = [$left, $right];
        }

        return $pairs;
    }

    private function uniqueTriples(
        array $leftIds,
        array $rightIds,
        array $thirdValues,
        int $count
    ): array {
        $leftCount = count($leftIds);
        $rightCount = count($rightIds);
        $thirdCount = count($thirdValues);
        if ($count <= 0 || $leftCount === 0 || $rightCount === 0 || $thirdCount === 0) {
            return [];
        }

        $max = $leftCount * $rightCount * $thirdCount;
        $target = min($count, $max);

        $triples = [];
        $used = [];
        $attempts = 0;
        $attemptLimit = $target * 20;

        while (count($triples) < $target && $attempts < $attemptLimit) {
            $left = $leftIds[array_rand($leftIds)];
            $right = $rightIds[array_rand($rightIds)];
            $third = $thirdValues[array_rand($thirdValues)];
            $key = $left.'-'.$right.'-'.$third;
            if (isset($used[$key])) {
                $attempts++;
                continue;
            }

            $used[$key] = true;
            $triples[] = [$left, $right, $third];
        }

        return $triples;
    }

    private function randomId(array $ids): ?int
    {
        if ($ids === []) {
            return null;
        }

        return $ids[array_rand($ids)];
    }

    private function randomOtherId(array $ids, int $excludeId): ?int
    {
        if (count($ids) < 2) {
            return null;
        }

        do {
            $selected = $this->randomId($ids);
        } while ($selected === $excludeId);

        return $selected;
    }

    private function randomSample(array $ids, int $count): array
    {
        if ($count <= 0 || $ids === []) {
            return [];
        }

        $total = count($ids);
        if ($count >= $total) {
            $shuffled = $ids;
            shuffle($shuffled);

            return $shuffled;
        }

        $keys = array_rand($ids, $count);
        if (! is_array($keys)) {
            $keys = [$keys];
        }

        return array_map(fn (int $key) => $ids[$key], $keys);
    }

    private function insertRows(string $table, array $rows, int $chunkSize = 500): void
    {
        if ($rows === []) {
            return;
        }

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
}
