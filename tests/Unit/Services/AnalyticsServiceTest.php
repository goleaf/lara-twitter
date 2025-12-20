<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_unique_skips_when_viewer_key_missing(): void
    {
        $originalSession = app('session');
        $session = Mockery::mock();
        $session->shouldReceive('getId')->andReturn(null);
        $this->app->instance('session', $session);

        $service = new AnalyticsService();
        $service->recordUnique('profile_view', 10);

        $this->assertDatabaseCount('analytics_uniques', 0);

        $this->app->instance('session', $originalSession);
    }

    public function test_record_unique_uses_guest_session_key(): void
    {
        $originalSession = app('session');
        $session = Mockery::mock();
        $session->shouldReceive('getId')->andReturn('guest-session');
        $this->app->instance('session', $session);

        $service = new AnalyticsService();
        $service->recordUnique('profile_view', 42);

        $expectedKey = 'guest:'.substr(hash('sha256', 'guest-session'), 0, 32);

        $this->assertDatabaseHas('analytics_uniques', [
            'type' => 'profile_view',
            'entity_id' => 42,
            'day' => now()->toDateString(),
            'viewer_key' => $expectedKey,
        ]);

        $this->app->instance('session', $originalSession);
    }

    public function test_record_unique_uses_authenticated_user_key(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $service = new AnalyticsService();
        $service->recordUnique('post_view', 99);

        $this->assertDatabaseHas('analytics_uniques', [
            'type' => 'post_view',
            'entity_id' => 99,
            'day' => now()->toDateString(),
            'viewer_key' => 'user:'.$user->id,
        ]);
    }
}
