<?php

namespace App\Livewire;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ReportsPage extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'all';

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
        $this->status = $this->normalizedStatus();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->status = $this->normalizedStatus();
    }

    private function normalizedStatus(): string
    {
        $allowed = array_merge(['all'], Report::statuses());

        return in_array($this->status, $allowed, true) ? $this->status : 'all';
    }

    public function getReportsProperty()
    {
        $query = Auth::user()
            ->reportsMade()
            ->with('reportable')
            ->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->paginate(20);
    }

    public function render()
    {
        return view('livewire.reports-page', [
            'statuses' => Report::statuses(),
        ]);
    }
}
