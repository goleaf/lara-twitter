<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Mockery;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_unique_skips_when_viewer_key_missing(): void
    {
        $this->mockSessionId(null);

        $service = new AnalyticsService();
        $service->recordUnique('profile_view', 10);

        $this->assertDatabaseCount('analytics_uniques', 0);
    }

    public function test_record_unique_uses_guest_session_key(): void
    {
        $this->mockSessionId('guest-session-id');

        $service = new AnalyticsService();
        $service->recordUnique('profile_view', 42);

        $expectedKey = 'guest:'.substr(hash('sha256', 'guest-session-id'), 0, 32);

        $this->assertDatabaseHas('analytics_uniques', [
            'type' => 'profile_view',
            'entity_id' => 42,
            'day' => now()->toDateString(),
            'viewer_key' => $expectedKey,
        ]);
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

    private function mockSessionId(?string $sessionId): void
    {
        $sessionManager = $this->app->make('session');
        $mock = Mockery::mock($sessionManager)->makePartial();
        $mock->shouldReceive('getId')->andReturn($sessionId);

        $this->app->instance('session', $mock);
        Session::swap($mock);
    }
}
