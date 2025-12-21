<?php

namespace Tests\Unit\Services;

use App\Models\Block;
use App\Models\Follow;
use App\Models\Mute;
use App\Models\MutedTerm;
use App\Models\User;
use App\Services\NotificationVisibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationVisibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_excludes_blocked_muted_and_term_matches(): void
    {
        $viewer = User::factory()->create();
        $blocked = User::factory()->create();
        $blockedBy = User::factory()->create();
        $muted = User::factory()->create();
        $followed = User::factory()->create();
        $stranger = User::factory()->create();

        Block::factory()->create([
            'blocker_id' => $viewer->id,
            'blocked_id' => $blocked->id,
        ]);

        Block::factory()->create([
            'blocker_id' => $blockedBy->id,
            'blocked_id' => $viewer->id,
        ]);

        Mute::factory()->create([
            'muter_id' => $viewer->id,
            'muted_id' => $muted->id,
        ]);

        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $followed->id,
        ]);

        MutedTerm::factory()->create([
            'user_id' => $viewer->id,
            'term' => 'spoiler',
            'mute_notifications' => true,
            'only_non_followed' => true,
            'expires_at' => null,
        ]);

        $blockedNotification = $this->makeNotification($viewer, [
            'type' => 'post_liked',
            'actor_user_id' => $blocked->id,
            'excerpt' => 'hello',
        ]);

        $blockedByNotification = $this->makeNotification($viewer, [
            'type' => 'post_liked',
            'actor_user_id' => $blockedBy->id,
            'excerpt' => 'hello',
        ]);

        $mutedNotification = $this->makeNotification($viewer, [
            'type' => 'post_liked',
            'actor_user_id' => $muted->id,
            'excerpt' => 'hello',
        ]);

        $messageNotification = $this->makeNotification($viewer, [
            'type' => 'message_received',
            'actor_user_id' => $muted->id,
            'excerpt' => 'spoiler ahead',
        ]);

        $termFilteredNotification = $this->makeNotification($viewer, [
            'type' => 'post_replied',
            'actor_user_id' => $stranger->id,
            'excerpt' => 'spoiler ahead',
        ]);

        $termAllowedNotification = $this->makeNotification($viewer, [
            'type' => 'post_replied',
            'actor_user_id' => $followed->id,
            'excerpt' => 'spoiler ahead',
        ]);

        $cleanNotification = $this->makeNotification($viewer, [
            'type' => 'post_liked',
            'actor_user_id' => $stranger->id,
            'excerpt' => 'hello',
        ]);

        $service = app(NotificationVisibilityService::class);

        $items = DatabaseNotification::query()
            ->where('notifiable_id', $viewer->id)
            ->get();

        $filtered = $service->filter($viewer, $items)->pluck('id')->all();

        $this->assertContains($messageNotification->id, $filtered);
        $this->assertContains($termAllowedNotification->id, $filtered);
        $this->assertContains($cleanNotification->id, $filtered);
        $this->assertNotContains($blockedNotification->id, $filtered);
        $this->assertNotContains($blockedByNotification->id, $filtered);
        $this->assertNotContains($mutedNotification->id, $filtered);
        $this->assertNotContains($termFilteredNotification->id, $filtered);
    }

    public function test_visible_unread_count_applies_filter(): void
    {
        $viewer = User::factory()->create();
        $blocked = User::factory()->create();

        Block::factory()->create([
            'blocker_id' => $viewer->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->makeNotification($viewer, [
            'type' => 'post_liked',
            'actor_user_id' => $blocked->id,
            'excerpt' => 'hello',
        ]);

        $this->makeNotification($viewer, [
            'type' => 'post_liked',
            'actor_user_id' => User::factory()->create()->id,
            'excerpt' => 'hello',
        ]);

        $service = app(NotificationVisibilityService::class);

        $this->assertSame(1, $service->visibleUnreadCount($viewer));
    }

    private function makeNotification(User $notifiable, array $data): DatabaseNotification
    {
        return DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'database',
            'notifiable_type' => $notifiable->getMorphClass(),
            'notifiable_id' => $notifiable->id,
            'data' => $data,
            'read_at' => null,
        ]);
    }
}
