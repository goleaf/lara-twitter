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
        $query = Post::query()
            ->whereHas('mentions', fn ($q) => $q->where('mentioned_user_id', Auth::id()))
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->latest();

        if (Auth::check()) {
            $exclude = Auth::user()->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.mentions-page')->layout('layouts.app');
    }
}
