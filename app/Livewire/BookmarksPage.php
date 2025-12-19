<?php

namespace App\Livewire;

use App\Models\Bookmark;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BookmarksPage extends Component
{
    use WithPagination;

    protected $listeners = [
        'bookmark-toggled' => '$refresh',
    ];

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

    public function remove(int $postId): void
    {
        abort_unless(Auth::check(), 403);

        DB::table('bookmarks')
            ->where('user_id', Auth::id())
            ->where('post_id', $postId)
            ->delete();
    }

    public function getBookmarksProperty()
    {
        $query = Bookmark::query()
            ->where('bookmarks.user_id', Auth::id())
            ->leftJoin('posts', 'bookmarks.post_id', '=', 'posts.id')
            ->select('bookmarks.*')
            ->latest('bookmarks.created_at');

        $exclude = Auth::user()->excludedUserIds();
        if ($exclude->isNotEmpty()) {
            $query->where(function (Builder $q) use ($exclude): void {
                $q->whereNull('posts.id')->orWhereNotIn('posts.user_id', $exclude);
            });
        }

        return $query
            ->with([
                'post' => fn ($q) => $q->with([
                    'user',
                    'images',
                    'replyTo.user',
                    'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
                ])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.bookmarks-page')->layout('layouts.app');
    }
}
