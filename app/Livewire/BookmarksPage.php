<?php

namespace App\Livewire;

use App\Models\Bookmark;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BookmarksPage extends Component
{
    use WithPagination;

    #[On('bookmark-toggled')]
    public function refreshBookmarks(): void
    {
        // No-op; Livewire re-renders after handling the event.
    }

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
        $viewer = Auth::user();

        $query = Bookmark::query()
            ->where('bookmarks.user_id', Auth::id())
            ->leftJoin('posts', 'bookmarks.post_id', '=', 'posts.id')
            ->select('bookmarks.*')
            ->latest('bookmarks.created_at');

        $exclude = $viewer->excludedUserIds();
        if ($exclude->isNotEmpty()) {
            $query->where(function (Builder $q) use ($exclude): void {
                $q->whereNull('posts.id')->orWhereNotIn('posts.user_id', $exclude);
            });
        }

        return $query
            ->with([
                'post' => fn ($q) => $q
                    ->select([
                        'id',
                        'user_id',
                        'body',
                        'reply_to_id',
                        'repost_of_id',
                        'reply_policy',
                        'created_at',
                        'location',
                        'video_path',
                        'video_mime_type',
                        'is_reply_like',
                    ])
                    ->withViewerContext($viewer)
                    ->with([
                        'user:id,name,username,avatar_path,is_verified',
                        'images:id,post_id,path,sort_order',
                        'linkPreview',
                        'poll.options' => fn ($q) => $q->withCount('votes'),
                        'replyTo:id,user_id',
                        'replyTo.user:id,username',
                        'repostOf' => fn ($q) => $q
                            ->select([
                                'id',
                                'user_id',
                                'body',
                                'reply_to_id',
                                'reply_policy',
                                'created_at',
                                'location',
                                'video_path',
                                'video_mime_type',
                                'is_reply_like',
                            ])
                            ->when($viewer, fn ($q) => $q->withViewerContext($viewer))
                            ->with([
                                'user:id,name,username,avatar_path,is_verified',
                                'images:id,post_id,path,sort_order',
                                'linkPreview',
                                'poll.options' => fn ($q) => $q->withCount('votes'),
                                'replyTo:id,user_id',
                                'replyTo.user:id,username',
                            ])
                            ->withCount(['likes', 'reposts', 'replies']),
                    ])
                    ->withCount(['likes', 'reposts', 'replies']),
            ])
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.bookmarks-page');
    }
}
