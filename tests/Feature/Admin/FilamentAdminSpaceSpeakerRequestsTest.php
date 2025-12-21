<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\SpaceSpeakerRequests\Pages\ListSpaceSpeakerRequests;
use App\Filament\Resources\SpaceSpeakerRequests\SpaceSpeakerRequestResource;
use App\Models\SpaceSpeakerRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminSpaceSpeakerRequestsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    public function test_admin_can_decide_and_reset_space_speaker_requests(): void
    {
        $admin = $this->actingAsAdmin();

        $pending = SpaceSpeakerRequest::factory()->create([
            'status' => SpaceSpeakerRequest::STATUS_PENDING,
            'decided_by' => null,
            'decided_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(ListSpaceSpeakerRequests::class)
            ->callTableAction('approve', $pending);

        $pending->refresh();

        $this->assertSame(SpaceSpeakerRequest::STATUS_APPROVED, $pending->status);
        $this->assertSame($admin->id, $pending->decided_by);
        $this->assertNotNull($pending->decided_at);

        Livewire::actingAs($admin)
            ->test(ListSpaceSpeakerRequests::class)
            ->callTableAction('reset-pending', $pending);

        $pending->refresh();

        $this->assertSame(SpaceSpeakerRequest::STATUS_PENDING, $pending->status);
        $this->assertNull($pending->decided_by);
        $this->assertNull($pending->decided_at);

        $another = SpaceSpeakerRequest::factory()->create(['status' => SpaceSpeakerRequest::STATUS_PENDING]);

        Livewire::actingAs($admin)
            ->test(ListSpaceSpeakerRequests::class)
            ->callTableAction('deny', $another);

        $another->refresh();

        $this->assertSame(SpaceSpeakerRequest::STATUS_DENIED, $another->status);
        $this->assertSame($admin->id, $another->decided_by);
        $this->assertNotNull($another->decided_at);
    }

    public function test_admin_can_bulk_approve_space_speaker_requests(): void
    {
        $admin = $this->actingAsAdmin();

        $requests = SpaceSpeakerRequest::factory()->count(2)->create([
            'status' => SpaceSpeakerRequest::STATUS_PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(ListSpaceSpeakerRequests::class)
            ->callTableBulkAction('approve', $requests->all());

        $this->assertSame(2, SpaceSpeakerRequest::query()
            ->where('status', SpaceSpeakerRequest::STATUS_APPROVED)
            ->where('decided_by', $admin->id)
            ->count());
    }

    public function test_navigation_badge_reflects_pending_speaker_requests(): void
    {
        Cache::flush();

        SpaceSpeakerRequest::factory()->count(2)->create(['status' => SpaceSpeakerRequest::STATUS_PENDING]);
        SpaceSpeakerRequest::factory()->create(['status' => SpaceSpeakerRequest::STATUS_APPROVED]);

        $this->assertSame('2', SpaceSpeakerRequestResource::getNavigationBadge());
        $this->assertSame('warning', SpaceSpeakerRequestResource::getNavigationBadgeColor());
    }
}
