<?php

namespace Tests\Unit\Models;

use App\Models\Report;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reason_helpers_expose_expected_values(): void
    {
        $labels = Report::reasonLabels();

        $this->assertSame(array_keys($labels), Report::reasons());
        $this->assertContains(Report::REASON_VIOLENCE, Report::reasonsRequiringDetails());
        $this->assertSame('Spam', Report::reasonLabel(Report::REASON_SPAM));
        $this->assertSame('unknown', Report::reasonLabel('unknown'));

        $options = Report::reasonOptions();
        $this->assertArrayHasKey('Spam & fake', $options);
        $this->assertArrayHasKey(Report::REASON_SPAM, $options['Spam & fake']);
    }

    public function test_statuses_list_all_known_states(): void
    {
        $this->assertSame([
            Report::STATUS_OPEN,
            Report::STATUS_REVIEWING,
            Report::STATUS_RESOLVED,
            Report::STATUS_DISMISSED,
        ], Report::statuses());
    }

    public function test_case_number_formats_with_padding(): void
    {
        $report = $this->createReport();

        $this->assertSame(sprintf('R-%08d', $report->id), $report->case_number);
    }

    public function test_resolved_status_sets_resolution_fields(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $report = $this->createReport([
            'status' => Report::STATUS_OPEN,
            'resolved_at' => null,
            'resolved_by' => null,
        ]);

        $report->status = Report::STATUS_RESOLVED;
        $report->save();
        $report->refresh();

        $this->assertNotNull($report->resolved_at);
        $this->assertSame($admin->id, $report->resolved_by);
    }

    public function test_dismissed_status_sets_resolution_fields(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $report = $this->createReport([
            'status' => Report::STATUS_OPEN,
            'resolved_at' => null,
            'resolved_by' => null,
        ]);

        $report->status = Report::STATUS_DISMISSED;
        $report->save();
        $report->refresh();

        $this->assertNotNull($report->resolved_at);
        $this->assertSame($admin->id, $report->resolved_by);
    }

    public function test_open_status_clears_resolution_fields(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $report = $this->createReport([
            'status' => Report::STATUS_RESOLVED,
            'resolved_at' => now()->subHour(),
            'resolved_by' => $admin->id,
        ]);

        $report->status = Report::STATUS_OPEN;
        $report->save();
        $report->refresh();

        $this->assertNull($report->resolved_at);
        $this->assertNull($report->resolved_by);
    }

    public function test_save_without_status_change_keeps_resolution_fields(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $resolvedAt = now()->subMinutes(5);

        $report = $this->createReport([
            'status' => Report::STATUS_RESOLVED,
            'resolved_at' => $resolvedAt,
            'resolved_by' => $admin->id,
        ]);

        $report->save();
        $report->refresh();

        $this->assertSame($admin->id, $report->resolved_by);
        $this->assertSame($resolvedAt->toDateTimeString(), $report->resolved_at?->toDateTimeString());
    }

    private function createReport(array $overrides = []): Report
    {
        $reporterId = $overrides['reporter_id'] ?? User::factory()->create()->id;
        $postId = $overrides['reportable_id'] ?? Post::factory()->create()->id;

        return Report::query()->create(array_merge([
            'reporter_id' => $reporterId,
            'reportable_type' => Post::class,
            'reportable_id' => $postId,
            'reason' => Report::REASON_SPAM,
            'details' => null,
            'status' => Report::STATUS_OPEN,
            'admin_notes' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ], $overrides));
    }
}
