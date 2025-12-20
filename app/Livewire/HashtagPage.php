<?php

namespace App\Livewire;

use App\Models\Hashtag;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class HashtagPage extends Component
{
    use WithPagination;

    #[Url]
    public string $tag = '';

    #[Url]
    public string $sort = 'latest';

    public function mount(string $tag): void
    {
        $this->tag = mb_strtolower($tag);
        $this->sort = $this->normalizedSort();

        $clean = ltrim($this->tag, '#');
        abort_unless((bool) preg_match('/^[\pL\pN][\pL\pN_]{0,49}$/u', $clean), 404);
        $this->tag = $clean;
    }

    public function updatedSort(): void
    {
        $this->resetPage();
        $this->sort = $this->normalizedSort();
    }

    private function normalizedSort(): string
    {
        return in_array($this->sort, ['latest', 'top'], true) ? $this->sort : 'latest';
    }

    public function getHashtagProperty(): ?Hashtag
    {
        return Hashtag::query()
            ->where('tag', $this->tag)
            ->withCount('posts')
            ->first();
    }

    public function getPostsProperty()
    {
        $viewer = Auth::user();

        $query = Post::query()
            ->whereHas('hashtags', fn ($q) => $q->where('tag', $this->tag))
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->withPostCardRelations($viewer, true);

        if ($viewer) {
            $exclude = $viewer->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }
        }

        if ($this->normalizedSort() === 'top') {
            return $query
                ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15);
        }

        return $query->latest()->orderByDesc('id')->paginate(15);
    }

    public function render()
    {
        return view('livewire.hashtag-page')->layout('layouts.app');
    }
}
