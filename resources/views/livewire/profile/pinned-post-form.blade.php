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
    <header class="space-y-1">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-base-content">{{ __('Pinned post') }}</h2>

            @if ($pinned_post_id)
                <span class="badge badge-outline">{{ __('Pinned') }} #{{ $pinned_post_id }}</span>
            @endif
        </div>

        <p class="text-sm opacity-70">{{ __('Pin one of your posts to the top of your profile.') }}</p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div>
            <x-input-label for="pinned_post_id" :value="__('Post ID')" />
            <input
                wire:model="pinned_post_id"
                id="pinned_post_id"
                type="number"
                min="1"
                class="input input-bordered input-sm w-full"
                placeholder="e.g. 123"
            />
            <x-input-error class="mt-2" :messages="$errors->get('pinned_post_id')" />
            <div class="text-xs opacity-70 mt-2">
                Tip: open the post and copy the number from its URL: <span class="font-mono">/posts/123</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <x-primary-button wire:loading.attr="disabled" wire:target="save">{{ __('Save') }}</x-primary-button>

            <button
                type="button"
                wire:click="clear"
                class="btn btn-ghost btn-sm"
                wire:loading.attr="disabled"
                wire:target="clear"
                @disabled(! $pinned_post_id)
            >
                {{ __('Unpin') }}
            </button>

            @if ($pinned_post_id)
                <a class="btn btn-ghost btn-sm" href="{{ route('posts.show', ['post' => $pinned_post_id]) }}" wire:navigate>
                    {{ __('Open post') }}
                </a>
            @endif

            <x-action-message class="me-3" on="pinned-post-updated">{{ __('Saved.') }}</x-action-message>
        </div>
    </form>
</section>
