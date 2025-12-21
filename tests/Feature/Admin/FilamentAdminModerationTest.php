<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Reports\Pages\ListReports;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\Space;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_resolve_and_unpublish_reported_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create(['is_published' => true]);
        $report = Report::factory()->create([
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'status' => Report::STATUS_OPEN,
        ]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        Livewire::test(ListReports::class)
            ->callTableAction('resolve-unpublish-post', $report);

        $this->assertFalse($post->fresh()->is_published);
        $this->assertSame(Report::STATUS_RESOLVED, $report->fresh()->status);
        $this->assertSame($admin->id, $report->fresh()->resolved_by);
    }

    public function test_admin_can_resolve_and_delete_reported_message(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $message = Message::factory()->create();
        $report = Report::factory()->create([
            'reportable_type' => Message::class,
            'reportable_id' => $message->id,
            'status' => Report::STATUS_OPEN,
        ]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        Livewire::test(ListReports::class)
            ->callTableAction('resolve-delete-message', $report);

        $message = Message::withTrashed()->find($message->id);

        $this->assertNotNull($message);
        $this->assertTrue($message->trashed());
        $this->assertSame(Report::STATUS_RESOLVED, $report->fresh()->status);
        $this->assertSame($admin->id, $report->fresh()->resolved_by);
    }

    public function test_admin_can_resolve_and_end_reported_space(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $space = Space::factory()->live()->create();
        $report = Report::factory()->create([
            'reportable_type' => Space::class,
            'reportable_id' => $space->id,
            'status' => Report::STATUS_OPEN,
        ]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        Livewire::test(ListReports::class)
            ->callTableAction('resolve-end-space', $report);

        $this->assertNotNull($space->fresh()->ended_at);
        $this->assertSame(Report::STATUS_RESOLVED, $report->fresh()->status);
        $this->assertSame($admin->id, $report->fresh()->resolved_by);
    }
}
