<?php

use App\Http\Requests\Profile\UpdatePinnedPostRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public ?int $pinned_post_id = null;

    public function mount(): void
    {
        $this->pinned_post_id = Auth::user()->pinned_post_id;
    }

    public function save(): void
    {
        $validated = $this->validate(UpdatePinnedPostRequest::rulesFor());

        $postId = $validated['pinned_post_id'] ?? null;

        if ($postId) {
            $owns = Post::query()->where('id', $postId)->where('user_id', Auth::id())->exists();
            if (! $owns) {
                $this->addError('pinned_post_id', 'You can only pin your own post.');
                return;
            }
        }

        Auth::user()->update(['pinned_post_id' => $postId]);
        $this->dispatch('pinned-post-updated');
    }

    public function clear(): void
    {
        Auth::user()->update(['pinned_post_id' => null]);
        $this->pinned_post_id = null;
        $this->dispatch('pinned-post-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Pinned post</h2>
        <p class="mt-1 text-sm text-gray-600">Pin one of your posts to the top of your profile.</p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div>
            <x-input-label for="pinned_post_id" value="Post ID" />
            <input
                wire:model="pinned_post_id"
                id="pinned_post_id"
                type="number"
                min="1"
                class="input input-bordered w-full"
                placeholder="e.g. 123"
            />
            <x-input-error class="mt-2" :messages="$errors->get('pinned_post_id')" />
            <div class="text-xs opacity-70 mt-2">
                Tip: open the post and copy the number from its URL: <span class="font-mono">/posts/123</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-primary-button>Save</x-primary-button>
            <button type="button" wire:click="clear" class="btn btn-ghost">Unpin</button>
            <x-action-message class="me-3" on="pinned-post-updated">Saved.</x-action-message>
        </div>
    </form>
</section>

