<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
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

    public function clearAll(): void
    {
        abort_unless(Auth::check(), 403);

        DB::table('bookmarks')->where('user_id', Auth::id())->delete();
        $this->resetPage();
    }

    public function getPostsProperty()
    {
        $query = Auth::user()
            ->bookmarkedPosts()
            ->getQuery()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->withCount(['likes', 'reposts', 'replies'])
            ->latest('bookmarks.created_at');

        $exclude = Auth::user()->excludedUserIds();
        if ($exclude->isNotEmpty()) {
            $query->whereNotIn('user_id', $exclude);
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.bookmarks-page')->layout('layouts.app');
    }
}
