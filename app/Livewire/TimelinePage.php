<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TimelinePage extends Component
{
    use WithPagination;

    #[Url]
    public string $feed = 'following';

    protected $listeners = [
        'post-created' => '$refresh',
    ];

    public function updatedFeed(): void
    {
        $this->resetPage();
    }

    private function normalizedFeed(): string
    {
        return in_array($this->feed, ['following', 'for-you'], true) ? $this->feed : 'following';
    }

    public function getPostsProperty()
    {
        $query = Post::query()
            ->whereNull('reply_to_id')
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts', 'replies']);

        if (Auth::check()) {
            $viewer = Auth::user();

            $mutedIds = $viewer->mutesInitiated()->pluck('muted_id');
            $blockedIds = $viewer->blocksInitiated()->pluck('blocked_id');
            $blockedByIds = $viewer->blocksReceived()->pluck('blocker_id');

            $exclude = $mutedIds->merge($blockedIds)->merge($blockedByIds)->unique()->values();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }
        }

        if ($this->normalizedFeed() === 'following') {
            $query->when(Auth::check(), function (Builder $query): void {
                $followingIds = Auth::user()->following()->pluck('users.id');
                $query->whereIn('user_id', $followingIds->push(Auth::id()));
            });

            return $query->latest()->paginate(15);
        }

        // "For You": include broader content, ranked by engagement + recency,
        // with a small bias towards followed accounts when signed in.
        $query->where('created_at', '>=', now()->subDays(7));

        if (Auth::check()) {
            $followingIds = Auth::user()->following()->pluck('users.id')->push(Auth::id())->all();
            $idsCsv = implode(',', array_map('intval', $followingIds));

            if ($idsCsv !== '') {
                $query->orderByRaw("case when user_id in ($idsCsv) then 1 else 0 end desc");
            }
        }

        return $query
            ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.timeline-page')->layout('layouts.app');
    }
}
