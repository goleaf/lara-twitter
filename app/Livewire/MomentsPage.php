<?php

namespace App\Livewire;

use App\Http\Requests\Moments\StoreMomentRequest;
use App\Models\Moment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class MomentsPage extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $title = '';

    public string $description = '';

    public bool $is_public = true;

    public $cover_image;

    public function getCanCreateProperty(): bool
    {
        $user = Auth::user();

        return $user && ($user->is_verified || $user->is_admin);
    }

    public function mount(): void
    {
    }

    public function create(): void
    {
        abort_unless($this->canCreate, 403);

        $validated = $this->validate(StoreMomentRequest::rulesFor());

        $coverPath = null;
        if ($this->cover_image) {
            $coverPath = $this->cover_image->storePublicly('moments/covers', ['disk' => 'public']);
        }

        $moment = Moment::query()->create([
            'owner_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'cover_image_path' => $coverPath,
            'is_public' => (bool) ($validated['is_public'] ?? true),
        ]);

        $this->reset(['title', 'description', 'is_public', 'cover_image']);

        $this->redirectRoute('moments.show', ['moment' => $moment], navigate: true);
    }

    public function getMomentsProperty()
    {
        if (! Auth::check()) {
            return collect();
        }

        return Auth::user()
            ->moments()
            ->select(['id', 'owner_id', 'title', 'description', 'cover_image_path', 'is_public', 'created_at'])
            ->withCount('items')
            ->latest()
            ->paginate(8, pageName: 'momentsPage');
    }

    public function getPublicMomentsProperty()
    {
        return Moment::query()
            ->select(['id', 'owner_id', 'title', 'description', 'cover_image_path', 'is_public', 'created_at'])
            ->where('is_public', true)
            ->with(['owner:id,name,username,avatar_path'])
            ->withCount('items')
            ->latest()
            ->paginate(8, pageName: 'publicMomentsPage');
    }

    public function render()
    {
        return view('livewire.moments-page');
    }
}
