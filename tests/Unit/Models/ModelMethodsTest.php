<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Moment;
use App\Models\MutedTerm;
use App\Models\Space;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModelMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_participant_helpers(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();

        $this->assertFalse($conversation->hasParticipant($user));
        $this->assertNull($conversation->participantFor($user));

        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue($conversation->hasParticipant($user));
        $this->assertNotNull($conversation->participantFor($user));
    }

    public function test_user_list_visibility_rules(): void
    {
        $owner = User::factory()->create();

        $public = UserList::factory()->create([
            'owner_id' => $owner->id,
            'is_private' => false,
        ]);

        $private = UserList::factory()->create([
            'owner_id' => $owner->id,
            'is_private' => true,
        ]);

        $this->assertTrue($public->isVisibleTo(null));
        $this->assertFalse($private->isVisibleTo(null));
        $this->assertTrue($private->isVisibleTo($owner));
    }

    public function test_moment_visibility_and_cover_url(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $moment = Moment::factory()->create([
            'owner_id' => $owner->id,
            'is_public' => false,
            'cover_image_path' => null,
        ]);

        $this->assertFalse($moment->isVisibleTo($viewer));
        $this->assertTrue($moment->isVisibleTo($owner));
        $this->assertNull($moment->coverUrl());

        $moment->cover_image_path = 'moments/example.jpg';
        $moment->save();

        $this->assertSame(Storage::disk('public')->url('moments/example.jpg'), $moment->coverUrl());
    }

    public function test_space_live_state_and_min_followers(): void
    {
        config(['spaces.min_followers_to_host' => 7]);

        $space = Space::factory()->create([
            'started_at' => now()->subMinute(),
            'ended_at' => null,
        ]);

        $this->assertSame(7, Space::minFollowersToHost());
        $this->assertTrue($space->isLive());
        $this->assertFalse($space->isEnded());

        $space->ended_at = now();
        $space->save();

        $this->assertFalse($space->isLive());
        $this->assertTrue($space->isEnded());
    }

    public function test_muted_term_active_state(): void
    {
        $active = MutedTerm::factory()->make(['expires_at' => null]);
        $future = MutedTerm::factory()->make(['expires_at' => now()->addDay()]);
        $past = MutedTerm::factory()->make(['expires_at' => now()->subDay()]);

        $this->assertTrue($active->isActive());
        $this->assertTrue($future->isActive());
        $this->assertFalse($past->isActive());
    }
}
