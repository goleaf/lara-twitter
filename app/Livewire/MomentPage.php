<?php

namespace App\Livewire;

use App\Http\Requests\Moments\AddMomentItemRequest;
use App\Models\Moment;
use App\Models\MomentItem;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MomentPage extends Component
{
    public Moment $moment;

    public int|string $post_id = '';

    public function mount(Moment $moment): void
    {
        abort_unless($moment->isVisibleTo(Auth::user()), 403);

        $this->moment = $moment->load(['owner']);
    }

    public function addPost(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->moment->owner_id, 403);

        $validated = $this->validate(AddMomentItemRequest::rulesFor());

        $max = (int) $this->moment->items()->max('sort_order');

        MomentItem::query()->firstOrCreate(
            ['moment_id' => $this->moment->id, 'post_id' => (int) $validated['post_id']],
            ['sort_order' => $max + 1],
        );

        $this->reset('post_id');
        $this->dispatch('$refresh');
    }

    public function removeItem(int $itemId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->moment->owner_id, 403);

        MomentItem::query()->where('id', $itemId)->where('moment_id', $this->moment->id)->delete();
        $this->dispatch('$refresh');
    }

    public function getItemsProperty()
    {
        return $this->moment
            ->items()
            ->with(['post' => fn ($q) => $q->with([
                'user',
                'images',
                'repostOf' => fn ($rq) => $rq->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])->withCount(['likes', 'reposts', 'replies'])])
            ->get();
    }

    public function render()
    {
        return view('livewire.moment-page')->layout('layouts.app');
    }
}

