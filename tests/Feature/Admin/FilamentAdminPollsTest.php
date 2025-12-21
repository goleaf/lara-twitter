<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\PostPollOptions\Pages\ListPostPollOptions;
use App\Filament\Resources\PostPolls\Pages\ListPostPolls;
use App\Models\PostPoll;
use App\Models\PostPollOption;
use App\Models\PostPollVote;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminPollsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    public function test_admin_can_end_poll_now(): void
    {
        $admin = $this->actingAsAdmin();

        $now = now()->startOfSecond();
        Carbon::setTestNow($now);

        $poll = PostPoll::factory()->create([
            'ends_at' => $now->copy()->addDay(),
        ]);

        Livewire::actingAs($admin)
            ->test(ListPostPolls::class)
            ->callTableAction('end-poll', $poll);

        $this->assertSame($now->toDateTimeString(), $poll->fresh()->ends_at->toDateTimeString());

        Carbon::setTestNow();
    }

    public function test_admin_can_clear_poll_option_votes(): void
    {
        $admin = $this->actingAsAdmin();

        $poll = PostPoll::factory()->create();
        $option = PostPollOption::factory()->create(['post_poll_id' => $poll->id]);
        $otherOption = PostPollOption::factory()->create(['post_poll_id' => $poll->id]);

        PostPollVote::factory()->count(2)->create([
            'post_poll_id' => $poll->id,
            'post_poll_option_id' => $option->id,
        ]);
        PostPollVote::factory()->create([
            'post_poll_id' => $poll->id,
            'post_poll_option_id' => $otherOption->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListPostPollOptions::class)
            ->callTableAction('clear-votes', $option);

        $this->assertSame(0, PostPollVote::query()->where('post_poll_option_id', $option->id)->count());
        $this->assertSame(1, PostPollVote::query()->where('post_poll_option_id', $otherOption->id)->count());
    }

    public function test_admin_can_bulk_end_polls(): void
    {
        $admin = $this->actingAsAdmin();

        $now = now()->startOfSecond();
        Carbon::setTestNow($now);

        $activePoll = PostPoll::factory()->create([
            'ends_at' => $now->copy()->addDay(),
        ]);
        $endedPoll = PostPoll::factory()->create([
            'ends_at' => $now->copy()->subDay(),
        ]);
        $endedAt = $endedPoll->ends_at->toDateTimeString();

        Livewire::actingAs($admin)
            ->test(ListPostPolls::class)
            ->callTableBulkAction('end-now', [$activePoll, $endedPoll]);

        $this->assertSame($now->toDateTimeString(), $activePoll->fresh()->ends_at->toDateTimeString());
        $this->assertSame($endedAt, $endedPoll->fresh()->ends_at->toDateTimeString());

        Carbon::setTestNow();
    }
}
