<?php

use App\Http\Requests\Profile\UpdateNotificationPreferencesRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $likes = true;
    public bool $reposts = true;
    public bool $replies = true;
    public bool $mentions = true;

    public function mount(): void
    {
        $user = Auth::user();
        $settings = $user->notification_settings ?? [];

        $this->likes = (bool) ($settings['likes'] ?? true);
        $this->reposts = (bool) ($settings['reposts'] ?? true);
        $this->replies = (bool) ($settings['replies'] ?? true);
        $this->mentions = (bool) ($settings['mentions'] ?? true);
    }

    public function save(): void
    {
        $validated = $this->validate(UpdateNotificationPreferencesRequest::rulesFor());

        $user = Auth::user();
        $user->notification_settings = [
            'likes' => (bool) ($validated['likes'] ?? false),
            'reposts' => (bool) ($validated['reposts'] ?? false),
            'replies' => (bool) ($validated['replies'] ?? false),
            'mentions' => (bool) ($validated['mentions'] ?? false),
        ];
        $user->save();

        $this->dispatch('notification-preferences-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Notifications') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Choose which activity should notify you (database notifications only).') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-4">
        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="likes" />
            <span class="text-sm">{{ __('Likes') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="reposts" />
            <span class="text-sm">{{ __('Retweets / Quotes') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="replies" />
            <span class="text-sm">{{ __('Replies') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="mentions" />
            <span class="text-sm">{{ __('Mentions') }}</span>
        </label>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="notification-preferences-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>

