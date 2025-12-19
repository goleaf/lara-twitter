<?php

namespace App\Livewire;

use App\Http\Requests\Reports\StoreReportRequest;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReportButton extends Component
{
    public string $reportableType;
    public int $reportableId;

    public bool $open = false;

    public string $reason = '';
    public string $details = '';

    public string $label = 'Report';

    public function mount(string $reportableType, int $reportableId, string $label = 'Report'): void
    {
        $this->reportableType = $reportableType;
        $this->reportableId = $reportableId;
        $this->label = $label;
    }

    public function openModal(): void
    {
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
        $this->reset(['reason', 'details']);
    }

    public function submit(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate(StoreReportRequest::rulesFor());

        $reportable = $this->resolveReportable();
        $this->ensureNotSelfReport($reportable);

        Report::query()->updateOrCreate(
            [
                'reporter_id' => Auth::id(),
                'reportable_type' => $this->reportableType,
                'reportable_id' => $this->reportableId,
            ],
            [
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?: null,
                'status' => Report::STATUS_OPEN,
            ],
        );

        $this->closeModal();
        $this->dispatch('report-submitted');
    }

    private function resolveReportable(): Post|User
    {
        if ($this->reportableType === Post::class) {
            return Post::query()->with('user')->findOrFail($this->reportableId);
        }

        if ($this->reportableType === User::class) {
            return User::query()->findOrFail($this->reportableId);
        }

        throw new ModelNotFoundException();
    }

    private function ensureNotSelfReport(Post|User $reportable): void
    {
        $reporterId = Auth::id();

        if ($reportable instanceof User && $reportable->id === $reporterId) {
            abort(422, 'You cannot report yourself.');
        }

        if ($reportable instanceof Post && $reportable->user_id === $reporterId) {
            abort(422, 'You cannot report your own post.');
        }
    }

    public function render()
    {
        return view('livewire.report-button', [
            'reasons' => Report::reasons(),
        ]);
    }
}

