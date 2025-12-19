<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MentionsPage extends Component
{
    use WithPagination;

    public function getPostsProperty()
    {
        return Post::query()
            ->whereHas('mentions', fn ($q) => $q->where('mentioned_user_id', Auth::id()))
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.mentions-page')->layout('layouts.app');
    }
}
