<?php

namespace App\Livewire;

use App\Http\Requests\Moments\AddMomentItemRequest;
use App\Http\Requests\Moments\UpdateMomentRequest;
use App\Models\Moment;
use App\Models\MomentItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MomentPage extends Component
{
    use WithFileUploads;

    public Moment $moment;

    public int|string $post_id = '';

    public string $caption = '';

    public int $editing_item_id = 0;

    public string $editing_caption = '';

    public string $title = '';

    public string $description = '';

    public bool $is_public = true;

    public $cover_image;

    public function mount(Moment $moment): void
    {
        abort_unless($moment->isVisibleTo(Auth::user()), 403);

        $this->moment = $moment->load(['owner']);

        $this->title = $this->moment->title;
        $this->description = (string) ($this->moment->description ?? '');
        $this->is_public = (bool) $this->moment->is_public;
    }

    public function addPost(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->moment->owner_id, 403);

        $this->post_id = $this->normalizePostId($this->post_id);
        $validated = $this->validate(AddMomentItemRequest::rulesFor());

        $caption = trim((string) ($validated['caption'] ?? ''));
        $caption = $caption === '' ? null : $caption;

        $max = (int) $this->moment->items()->max('sort_order');

        $existing = MomentItem::query()
            ->where('moment_id', $this->moment->id)
            ->where('post_id', (int) $validated['post_id'])
            ->first();

        if ($existing) {
            if ($caption !== null) {
                $existing->update(['caption' => $caption]);
            }
        } else {
            MomentItem::query()->create([
                'moment_id' => $this->moment->id,
                'post_id' => (int) $validated['post_id'],
                'caption' => $caption,
                'sort_order' => $max + 1,
            ]);
        }

        $this->reset(['post_id', 'caption']);
        $this->dispatch('$refresh');
    }

    public function updateMoment(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->moment->owner_id, 403);

        $validated = $this->validate(UpdateMomentRequest::rulesFor());

        $updates = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'is_public' => (bool) ($validated['is_public'] ?? $this->moment->is_public),
        ];

        if ($this->cover_image) {
            $path = $this->cover_image->storePublicly('moments/covers', ['disk' => 'public']);
            $updates['cover_image_path'] = $path;

            if ($this->moment->cover_image_path) {
                Storage::disk('public')->delete($this->moment->cover_image_path);
            }
        }

        $this->moment->update($updates);
        $this->moment->refresh()->load('owner');

        $this->reset('cover_image');
    }

    public function removeItem(int $itemId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->moment->owner_id, 403);

        MomentItem::query()->where('id', $itemId)->where('moment_id', $this->moment->id)->delete();
        $this->dispatch('$refresh');
    }

    public function startEditingCaption(int $itemId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->moment->owner_id, 403);

        $item = MomentItem::query()
            ->where('id', $itemId)
            ->where('moment_id', $this->moment->id)
            ->firstOrFail();

        $this->editing_item_id = $itemId;
        $this->editing_caption = (string) ($item->caption ?? '');
    }

    public function saveCaption(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->moment->owner_id, 403);

        if ($this->editing_item_id === 0) {
            return;
        }

        $validated = $this->validate([
            'editing_caption' => ['nullable', 'string', 'max:280'],
        ]);

        $caption = trim((string) ($validated['editing_caption'] ?? ''));
        $caption = $caption === '' ? null : $caption;

        MomentItem::query()
            ->where('id', $this->editing_item_id)
            ->where('moment_id', $this->moment->id)
            ->update(['caption' => $caption]);

        $this->reset(['editing_item_id', 'editing_caption']);
        $this->dispatch('$refresh');
    }

    public function cancelEditingCaption(): void
    {
        $this->reset(['editing_item_id', 'editing_caption']);
    }

    public function moveItemUp(int $itemId): void
    {
        $this->moveItem($itemId, -1);
    }

    public function moveItemDown(int $itemId): void
    {
        $this->moveItem($itemId, 1);
    }

    private function moveItem(int $itemId, int $direction): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->moment->owner_id, 403);

        $item = MomentItem::query()
            ->where('id', $itemId)
            ->where('moment_id', $this->moment->id)
            ->firstOrFail();

        $swapQuery = MomentItem::query()->where('moment_id', $this->moment->id);
        if ($direction < 0) {
            $swapQuery
                ->where('sort_order', '<', $item->sort_order)
                ->orderByDesc('sort_order');
        } else {
            $swapQuery
                ->where('sort_order', '>', $item->sort_order)
                ->orderBy('sort_order');
        }

        $swap = $swapQuery->first();

        if (! $swap) {
            return;
        }

        $a = $item->sort_order;
        $b = $swap->sort_order;

        $item->update(['sort_order' => -1000000]);
        $swap->update(['sort_order' => $a]);
        $item->update(['sort_order' => $b]);

        $this->dispatch('$refresh');
    }

    public function getItemsProperty()
    {
        $viewer = Auth::user();

        return $this->moment
            ->items()
            ->with(['post' => fn ($q) => $q->withPostCardRelations($viewer, true)])
            ->get();
    }

    private function normalizePostId(int|string $value): int|string
    {
        if (is_int($value)) {
            return $value;
        }

        $v = trim((string) $value);

        if (preg_match('/\\bposts\\/(\\d+)\\b/', $v, $m)) {
            return (int) $m[1];
        }

        if (ctype_digit($v)) {
            return (int) $v;
        }

        return $value;
    }

    public function render()
    {
        return view('livewire.moment-page')->layout('layouts.app');
    }
}
