<?php

namespace Tests\Feature;

use App\Livewire\ReportButton;
use App\Livewire\ReportsPage;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_reports_page(): void
    {
        Livewire::test(ReportsPage::class)
            ->assertStatus(403);
    }

    public function test_user_can_view_their_reports_and_case_numbers(): void
    {
        $author = User::factory()->create();
        $reporter = User::factory()->create();
        $otherReporter = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        Livewire::actingAs($reporter)
            ->test(ReportButton::class, [
                'reportableType' => Post::class,
                'reportableId' => $post->id,
            ])
            ->set('reason', Report::REASON_SPAM)
            ->call('submit');

        $myReport = Report::query()
            ->where('reporter_id', $reporter->id)
            ->firstOrFail();

        Livewire::actingAs($otherReporter)
            ->test(ReportButton::class, [
                'reportableType' => Post::class,
                'reportableId' => $post->id,
            ])
            ->set('reason', Report::REASON_OTHER)
            ->set('details', 'Suspicious behavior.')
            ->call('submit');

        $othersReport = Report::query()
            ->where('reporter_id', $otherReporter->id)
            ->firstOrFail();

        Livewire::actingAs($reporter)
            ->test(ReportsPage::class)
            ->assertSee($myReport->case_number)
            ->assertSee(Report::reasonLabel($myReport->reason))
            ->assertDontSee($othersReport->case_number);
    }
}

