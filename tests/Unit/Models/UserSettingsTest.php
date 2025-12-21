<?php

namespace Tests\Unit\Models;

use App\Models\Block;
use App\Models\Follow;
use App\Models\Mute;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_settings_and_policy_lists(): void
    {
        $user = User::factory()->make();

        $this->assertFalse($user->timelineSetting('hide_reposts', false));
        $user->timeline_settings = ['hide_reposts' => true];
        $this->assertTrue($user->timelineSetting('hide_reposts', false));

        $this->assertContains(User::DM_EVERYONE, User::dmPolicies());
        $this->assertContains(User::DM_FOLLOWING, User::dmPolicies());
        $this->assertContains(User::DM_NONE, User::dmPolicies());

        $this->assertContains(User::BIRTH_DATE_PUBLIC, User::birthDateVisibilities());
        $this->assertContains(User::BIRTH_DATE_FOLLOWERS, User::birthDateVisibilities());
        $this->assertContains(User::BIRTH_DATE_PRIVATE, User::birthDateVisibilities());
    }

    public function test_birth_date_visibility_rules(): void
    {
        $owner = User::factory()->create(['birth_date' => null]);
        $viewer = User::factory()->create();

        $this->assertFalse($owner->canShowBirthDateTo($viewer));

        $owner->birth_date = '1990-01-01';
        $owner->birth_date_visibility = User::BIRTH_DATE_PUBLIC;
        $owner->save();

        $this->assertTrue($owner->canShowBirthDateTo($viewer));
        $this->assertTrue($owner->canShowBirthDateTo($owner));

        $owner->birth_date_visibility = User::BIRTH_DATE_PRIVATE;
        $owner->save();

        $this->assertFalse($owner->canShowBirthDateTo($viewer));

        $owner->birth_date_visibility = User::BIRTH_DATE_FOLLOWERS;
        $owner->save();

        $this->assertFalse($owner->canShowBirthDateTo($viewer));

        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $owner->id,
        ]);

        $this->assertTrue($owner->canShowBirthDateTo($viewer));
    }

    public function test_can_access_filament_panels(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $adminPanel = Panel::make()->id('admin');
        $otherPanel = Panel::make()->id('other');

        $this->assertFalse($user->canAccessPanel($adminPanel));
        $this->assertFalse($user->canAccessPanel($otherPanel));
        $this->assertTrue($admin->canAccessPanel($adminPanel));
        $this->assertFalse($admin->canAccessPanel($otherPanel));
    }

    public function test_avatar_and_header_urls(): void
    {
        Storage::fake('public');

        $user = User::factory()->make([
            'avatar_path' => null,
            'header_path' => null,
        ]);

        $this->assertNull($user->avatar_url);
        $this->assertNull($user->header_url);

        $user->avatar_path = 'avatars/test.jpg';
        $user->header_path = 'headers/test.jpg';

        $this->assertSame(Storage::disk('public')->url('avatars/test.jpg'), $user->avatar_url);
        $this->assertSame(Storage::disk('public')->url('headers/test.jpg'), $user->header_url);
    }

    public function test_notification_preferences_and_quiet_hours(): void
    {
        $user = User::factory()->make();

        $this->assertTrue($user->wantsNotification('likes'));
        $this->assertFalse($user->wantsNotification('followed_posts'));
        $this->assertTrue($user->wantsNotification('unknown'));

        $user->notification_settings = ['likes' => false, 'email_enabled' => true];
        $this->assertFalse($user->wantsNotification('likes'));
        $this->assertTrue($user->wantsEmailNotifications());

        $user->notification_settings = ['quiet_hours_enabled' => false];
        $this->assertFalse($user->isInNotificationQuietHours());

        $user->notification_settings = [
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '99:99',
            'quiet_hours_end' => '07:00',
        ];
        $this->assertFalse($user->isInNotificationQuietHours());

        Carbon::setTestNow('2025-01-01 12:00:00');
        $user->notification_settings = [
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '12:00',
            'quiet_hours_end' => '12:00',
        ];
        $this->assertTrue($user->isInNotificationQuietHours());

        Carbon::setTestNow('2025-01-01 23:00:00');
        $user->notification_settings = [
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
        ];
        $this->assertTrue($user->isInNotificationQuietHours());

        Carbon::setTestNow('2025-01-01 13:30:00');
        $user->notification_settings = [
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '13:00',
            'quiet_hours_end' => '17:00',
        ];
        $this->assertTrue($user->isInNotificationQuietHours());

        Carbon::setTestNow();
    }

    public function test_should_send_notification_email_requires_verified_and_not_quiet(): void
    {
        Carbon::setTestNow('2025-01-01 09:00:00');

        $user = User::factory()->make([
            'email_verified_at' => now(),
            'notification_settings' => [
                'email_enabled' => true,
                'quiet_hours_enabled' => false,
            ],
        ]);

        $this->assertTrue($user->shouldSendNotificationEmail());

        $user->email_verified_at = null;
        $this->assertFalse($user->shouldSendNotificationEmail());

        Carbon::setTestNow();
    }

    public function test_allows_notification_from_respects_blocks_mutes_and_filters(): void
    {
        $viewer = User::factory()->create([
            'notification_settings' => [
                'only_verified' => true,
                'only_following' => true,
                'quality_filter' => true,
            ],
        ]);

        $actor = User::factory()->create([
            'is_verified' => false,
            'avatar_path' => null,
            'email_verified_at' => null,
        ]);

        $this->assertFalse($viewer->allowsNotificationFrom($viewer));

        Block::factory()->create([
            'blocker_id' => $viewer->id,
            'blocked_id' => $actor->id,
        ]);

        $this->assertFalse($viewer->allowsNotificationFrom($actor));

        Block::query()->delete();

        Mute::factory()->create([
            'muter_id' => $viewer->id,
            'muted_id' => $actor->id,
        ]);

        $this->assertFalse($viewer->allowsNotificationFrom($actor));

        Mute::query()->delete();

        $this->assertFalse($viewer->allowsNotificationFrom($actor));

        $actor->is_verified = true;
        $actor->avatar_path = 'avatars/ok.jpg';
        $actor->email_verified_at = now();
        $actor->save();

        $this->assertFalse($viewer->allowsNotificationFrom($actor));

        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $actor->id,
        ]);

        $this->assertTrue($viewer->allowsNotificationFrom($actor));
    }
}
