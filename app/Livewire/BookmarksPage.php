<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BookmarksPage extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
    }

    public function getPostsProperty()
    {
        return Auth::user()
            ->bookmarkedPosts()
            ->getQuery()
            ->whereNull('reply_to_id')
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->withCount(['likes', 'reposts', 'replies'])
            ->latest('bookmarks.created_at')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.bookmarks-page')->layout('layouts.app');
    }
}

