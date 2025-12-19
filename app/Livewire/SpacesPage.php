<?php

namespace App\Livewire;

use App\Http\Requests\Spaces\StoreSpaceRequest;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SpacesPage extends Component
{
    public string $title = '';

    public string $description = '';

    public string $scheduled_for = '';

    public function create(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate(StoreSpaceRequest::rulesFor());

        $space = Space::query()->create([
            'host_user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'scheduled_for' => $validated['scheduled_for'] ?: null,
        ]);

        $this->redirectRoute('spaces.show', ['space' => $space], navigate: true);
    }

    public function getLiveSpacesProperty()
    {
        return Space::query()
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->with(['host'])
            ->latest('started_at')
            ->get();
    }

    public function getUpcomingSpacesProperty()
    {
        return Space::query()
            ->whereNull('started_at')
            ->whereNull('ended_at')
            ->with(['host'])
            ->orderBy('scheduled_for')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.spaces-page')->layout('layouts.app');
    }
}

