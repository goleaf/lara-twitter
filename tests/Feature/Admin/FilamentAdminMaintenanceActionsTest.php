<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Spaces\Pages\ListSpaces;
use App\Models\Post;
use App\Models\Report;
use App\Models\Space;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminMaintenanceActionsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    public function test_admin_can_clear_post_reports_from_table(): void
    {
        $admin = $this->actingAsAdmin();

        $post = Post::factory()->create();
        $otherPost = Post::factory()->create();

        Report::factory()->count(2)->create([
            'reportable_id' => $post->id,
        ]);
        Report::factory()->create([
            'reportable_id' => $otherPost->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListPosts::class)
            ->callTableAction('clear-reports', $post);

        $this->assertSame(0, Report::query()->where('reportable_id', $post->id)->count());
        $this->assertSame(1, Report::query()->where('reportable_id', $otherPost->id)->count());
    }

    public function test_admin_can_bulk_clear_post_reports(): void
    {
        $admin = $this->actingAsAdmin();

        $postA = Post::factory()->create();
        $postB = Post::factory()->create();

        Report::factory()->create(['reportable_id' => $postA->id]);
        Report::factory()->create(['reportable_id' => $postB->id]);

        Livewire::actingAs($admin)
            ->test(ListPosts::class)
            ->callTableBulkAction('clear-reports', [$postA, $postB]);

        $this->assertSame(0, Report::query()->where('reportable_id', $postA->id)->count());
        $this->assertSame(0, Report::query()->where('reportable_id', $postB->id)->count());
    }

    public function test_admin_can_bulk_start_spaces(): void
    {
        $admin = $this->actingAsAdmin();

        $now = now()->startOfSecond();
        Carbon::setTestNow($now);

        $scheduled = Space::factory()->scheduled()->create();
        $ended = Space::factory()->ended()->create();
        $endedStartedAt = $ended->started_at;

        Livewire::actingAs($admin)
            ->test(ListSpaces::class)
            ->callTableBulkAction('start-now', [$scheduled, $ended]);

        $this->assertSame($now->toDateTimeString(), $scheduled->fresh()->started_at->toDateTimeString());
        $this->assertSame($endedStartedAt?->toDateTimeString(), $ended->fresh()->started_at?->toDateTimeString());

        Carbon::setTestNow();
    }

    public function test_admin_can_bulk_end_spaces(): void
    {
        $admin = $this->actingAsAdmin();

        $now = now()->startOfSecond();
        Carbon::setTestNow($now);

        $live = Space::factory()->live()->create();
        $scheduled = Space::factory()->scheduled()->create();

        Livewire::actingAs($admin)
            ->test(ListSpaces::class)
            ->callTableBulkAction('end-now', [$live, $scheduled]);

        $this->assertSame($now->toDateTimeString(), $live->fresh()->ended_at->toDateTimeString());
        $this->assertNull($scheduled->fresh()->ended_at);

        Carbon::setTestNow();
    }
}
