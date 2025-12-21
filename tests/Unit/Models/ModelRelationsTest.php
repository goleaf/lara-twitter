<?php

namespace Tests\Unit\Models;

use App\Models\Bookmark;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Follow;
use App\Models\Hashtag;
use App\Models\Like;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageReaction;
use App\Models\Moment;
use App\Models\MomentItem;
use App\Models\MutedTerm;
use App\Models\PostImage;
use App\Models\PostLinkPreview;
use App\Models\PostPoll;
use App\Models\PostPollOption;
use App\Models\PostPollVote;
use App\Models\Space;
use App\Models\SpaceParticipant;
use App\Models\SpaceReaction;
use App\Models\SpaceSpeakerRequest;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    public function test_relation_methods_return_expected_relation_types(): void
    {
        $cases = [
            [new Bookmark(), ['user' => BelongsTo::class, 'post' => BelongsTo::class]],
            [new Follow(), ['follower' => BelongsTo::class, 'followed' => BelongsTo::class]],
            [new Like(), ['user' => BelongsTo::class, 'post' => BelongsTo::class]],
            [new Hashtag(), ['posts' => BelongsToMany::class, 'reports' => MorphMany::class]],
            [new MomentItem(), ['moment' => BelongsTo::class, 'post' => BelongsTo::class]],
            [new MutedTerm(), ['user' => BelongsTo::class]],
            [new PostImage(), ['post' => BelongsTo::class]],
            [new PostLinkPreview(), ['post' => BelongsTo::class]],
            [new PostPoll(), ['post' => BelongsTo::class, 'options' => HasMany::class, 'votes' => HasMany::class]],
            [new PostPollOption(), ['poll' => BelongsTo::class, 'votes' => HasMany::class]],
            [new PostPollVote(), ['poll' => BelongsTo::class, 'option' => BelongsTo::class, 'user' => BelongsTo::class]],
            [new ConversationParticipant(), ['conversation' => BelongsTo::class, 'user' => BelongsTo::class]],
            [new MessageAttachment(), ['message' => BelongsTo::class]],
            [new MessageReaction(), ['message' => BelongsTo::class, 'user' => BelongsTo::class]],
            [new Message(), ['conversation' => BelongsTo::class, 'user' => BelongsTo::class, 'attachments' => HasMany::class, 'reactions' => HasMany::class, 'reports' => MorphMany::class]],
            [new SpaceParticipant(), ['space' => BelongsTo::class, 'user' => BelongsTo::class]],
            [new SpaceReaction(), ['space' => BelongsTo::class, 'user' => BelongsTo::class]],
            [new SpaceSpeakerRequest(), ['space' => BelongsTo::class, 'user' => BelongsTo::class, 'decidedBy' => BelongsTo::class]],
            [new Conversation(), ['createdBy' => BelongsTo::class, 'participants' => HasMany::class, 'messages' => HasMany::class]],
            [new Space(), ['host' => BelongsTo::class, 'pinnedPost' => BelongsTo::class, 'participants' => HasMany::class, 'reactions' => HasMany::class, 'speakerRequests' => HasMany::class, 'reports' => MorphMany::class]],
            [new UserList(), ['owner' => BelongsTo::class, 'members' => BelongsToMany::class, 'subscribers' => BelongsToMany::class, 'reports' => MorphMany::class]],
            [new Moment(), ['owner' => BelongsTo::class, 'items' => HasMany::class, 'firstItem' => HasOne::class]],
            [new User(), [
                'likes' => HasMany::class,
                'conversationParticipants' => HasMany::class,
                'conversations' => HasManyThrough::class,
                'messages' => HasMany::class,
                'bookmarks' => HasMany::class,
                'spacesHosted' => HasMany::class,
                'spaceParticipants' => HasMany::class,
            ]],
        ];

        foreach ($cases as [$model, $relations]) {
            foreach ($relations as $method => $class) {
                $this->assertInstanceOf($class, $model->{$method}());
            }
        }
    }

    public function test_casts_include_expected_keys(): void
    {
        $this->assertSame('boolean', (new Conversation())->getCasts()['is_group']);

        $participantCasts = (new ConversationParticipant())->getCasts();
        $this->assertSame('datetime', $participantCasts['last_read_at']);
        $this->assertSame('boolean', $participantCasts['is_request']);
        $this->assertSame('boolean', $participantCasts['is_pinned']);

        $this->assertSame('datetime', (new PostPoll())->getCasts()['ends_at']);
        $this->assertSame('datetime', (new PostLinkPreview())->getCasts()['fetched_at']);

        $spaceParticipantCasts = (new SpaceParticipant())->getCasts();
        $this->assertSame('datetime', $spaceParticipantCasts['joined_at']);
        $this->assertSame('datetime', $spaceParticipantCasts['left_at']);

        $spaceCasts = (new Space())->getCasts();
        $this->assertSame('datetime', $spaceCasts['scheduled_for']);
        $this->assertSame('boolean', $spaceCasts['recording_enabled']);
        $this->assertSame('datetime', $spaceCasts['started_at']);
        $this->assertSame('datetime', $spaceCasts['ended_at']);
        $this->assertSame('datetime', $spaceCasts['recording_available_until']);

        $this->assertSame('boolean', (new UserList())->getCasts()['is_private']);
        $this->assertSame('boolean', (new Moment())->getCasts()['is_public']);
        $this->assertSame('datetime', (new SpaceSpeakerRequest())->getCasts()['decided_at']);

        $mutedTermCasts = (new MutedTerm())->getCasts();
        $this->assertSame('boolean', $mutedTermCasts['whole_word']);
        $this->assertSame('boolean', $mutedTermCasts['only_non_followed']);
        $this->assertSame('boolean', $mutedTermCasts['mute_timeline']);
        $this->assertSame('boolean', $mutedTermCasts['mute_notifications']);
        $this->assertSame('datetime', $mutedTermCasts['expires_at']);
    }
}
