<?php

namespace Tests\Unit\Services;

use App\Models\Block;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Follow;
use App\Models\User;
use App\Services\DirectMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectMessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_for_disallows_when_dm_none(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create(['dm_policy' => User::DM_NONE]);

        $service = app(DirectMessageService::class);

        $policy = $service->policyFor($sender, $recipient);

        $this->assertSame(['allowed' => false, 'is_request' => false], $policy);
    }

    public function test_policy_for_allows_when_recipient_follows_sender(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        Follow::factory()->create([
            'follower_id' => $recipient->id,
            'followed_id' => $sender->id,
        ]);

        $service = app(DirectMessageService::class);

        $policy = $service->policyFor($sender, $recipient);

        $this->assertSame(['allowed' => true, 'is_request' => false], $policy);
    }

    public function test_policy_for_disallows_when_requests_disabled(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create(['dm_allow_requests' => false]);

        $service = app(DirectMessageService::class);

        $policy = $service->policyFor($sender, $recipient);

        $this->assertSame(['allowed' => false, 'is_request' => false], $policy);
    }

    public function test_policy_for_allows_request_when_not_following(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create(['dm_allow_requests' => true]);

        $service = app(DirectMessageService::class);

        $policy = $service->policyFor($sender, $recipient);

        $this->assertSame(['allowed' => true, 'is_request' => true], $policy);
    }

    public function test_find_or_create_throws_for_same_user(): void
    {
        $user = User::factory()->create();

        $service = app(DirectMessageService::class);

        $this->expectException(\InvalidArgumentException::class);

        $service->findOrCreate($user, $user);
    }

    public function test_find_or_create_throws_when_blocked(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        Block::factory()->create([
            'blocker_id' => $recipient->id,
            'blocked_id' => $sender->id,
        ]);

        $service = app(DirectMessageService::class);

        $this->expectException(\RuntimeException::class);

        $service->findOrCreate($sender, $recipient);
    }

    public function test_find_or_create_throws_when_policy_disallows(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create(['dm_policy' => User::DM_NONE]);

        $service = app(DirectMessageService::class);

        $this->expectException(\RuntimeException::class);

        $service->findOrCreate($sender, $recipient);
    }

    public function test_find_or_create_returns_existing_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $conversation = Conversation::factory()->create([
            'created_by_user_id' => $sender->id,
            'is_group' => false,
        ]);

        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'is_request' => false,
        ]);

        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $recipient->id,
            'is_request' => false,
        ]);

        $service = app(DirectMessageService::class);

        $result = $service->findOrCreate($sender, $recipient);

        $this->assertTrue($result->is($conversation));
    }

    public function test_find_or_create_creates_request_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create(['dm_allow_requests' => true]);

        $service = app(DirectMessageService::class);

        $conversation = $service->findOrCreate($sender, $recipient);

        $this->assertFalse($conversation->is_group);
        $this->assertTrue($conversation->participants()->where('user_id', $recipient->id)->value('is_request'));
        $this->assertFalse($conversation->participants()->where('user_id', $sender->id)->value('is_request'));
    }
}
