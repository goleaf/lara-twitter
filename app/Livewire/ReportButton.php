<?php

namespace App\Livewire;

use App\Http\Requests\Reports\StoreReportRequest;
use App\Models\Hashtag;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\Space;
use App\Models\User;
use App\Models\UserList;
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

    public ?string $submittedCaseNumber = null;

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

    public function clearNotice(): void
    {
        $this->submittedCaseNumber = null;
    }

    public function submit(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate(StoreReportRequest::rulesFor($this->reason));

        $reportable = $this->resolveReportable();
        $this->authorizeReportable($reportable);
        $this->ensureNotSelfReport($reportable);

        $report = Report::query()->updateOrCreate(
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

        $this->submittedCaseNumber = $report->case_number;

        $this->closeModal();
        $this->dispatch('report-submitted');
    }

    private function resolveReportable(): Post|User|Hashtag|Message|UserList|Space
    {
        if ($this->reportableType === Post::class) {
            return Post::query()->with('user')->findOrFail($this->reportableId);
        }

        if ($this->reportableType === User::class) {
            return User::query()->findOrFail($this->reportableId);
        }

        if ($this->reportableType === Hashtag::class) {
            return Hashtag::query()->findOrFail($this->reportableId);
        }

        if ($this->reportableType === Message::class) {
            return Message::query()->with(['conversation'])->findOrFail($this->reportableId);
        }

        if ($this->reportableType === UserList::class) {
            return UserList::query()->findOrFail($this->reportableId);
        }

        if ($this->reportableType === Space::class) {
            return Space::query()->findOrFail($this->reportableId);
        }

        throw new ModelNotFoundException;
    }

    private function ensureNotSelfReport(Post|User|Hashtag|Message|UserList|Space $reportable): void
    {
        $reporterId = Auth::id();

        if ($reportable instanceof User && $reportable->id === $reporterId) {
            abort(422, 'You cannot report yourself.');
        }

        if ($reportable instanceof Post && $reportable->user_id === $reporterId) {
            abort(422, 'You cannot report your own post.');
        }

        if ($reportable instanceof Message && $reportable->user_id === $reporterId) {
            abort(422, 'You cannot report your own message.');
        }

        if ($reportable instanceof UserList && $reportable->owner_id === $reporterId) {
            abort(422, 'You cannot report your own list.');
        }

        if ($reportable instanceof Space && $reportable->host_user_id === $reporterId) {
            abort(422, 'You cannot report your own space.');
        }
    }

    private function authorizeReportable(Post|User|Hashtag|Message|UserList|Space $reportable): void
    {
        if (! Auth::check()) {
            abort(403);
        }

        if ($reportable instanceof Message) {
            abort_unless($reportable->conversation && $reportable->conversation->hasParticipant(Auth::user()), 403);
        }

        if ($reportable instanceof UserList) {
            abort_unless($reportable->isVisibleTo(Auth::user()), 403);
        }
    }

    public function render()
    {
        return view('livewire.report-button', [
            'reasonOptions' => Report::reasonOptions(),
        ]);
    }
}
