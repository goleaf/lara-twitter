<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class MentionsPage extends Component
{
    use WithPagination;

    public function getPostsProperty()
    {
        $viewer = Auth::user();

        $query = Post::query()
            ->whereHas('mentions', fn ($q) => $q->where('mentioned_user_id', Auth::id()))
            ->withPostCardRelations($viewer)
            ->latest()
            ->orderByDesc('id');

        if ($viewer) {
            $exclude = $viewer->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.mentions-page');
    }
}
