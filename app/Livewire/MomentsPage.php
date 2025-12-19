<?php

namespace App\Livewire;

use App\Http\Requests\Moments\StoreMomentRequest;
use App\Models\Moment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MomentsPage extends Component
{
    public string $title = '';

    public string $description = '';

    public bool $is_public = true;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
    }

    public function create(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate(StoreMomentRequest::rulesFor());

        $moment = Moment::query()->create([
            'owner_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'is_public' => (bool) ($validated['is_public'] ?? true),
        ]);

        $this->redirectRoute('moments.show', ['moment' => $moment], navigate: true);
    }

    public function getMomentsProperty()
    {
        return Auth::user()
            ->moments()
            ->latest()
            ->withCount('items')
            ->get();
    }

    public function render()
    {
        return view('livewire.moments-page')->layout('layouts.app');
    }
}

