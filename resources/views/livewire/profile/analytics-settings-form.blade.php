<?php

use App\Http\Requests\Profile\UpdateAnalyticsSettingsRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $analytics_enabled = false;

    public function mount(): void
    {
        $this->analytics_enabled = (bool) (Auth::user()->analytics_enabled ?? false);
    }

    public function save(): void
    {
        $validated = $this->validate(UpdateAnalyticsSettingsRequest::rulesFor());

        $user = Auth::user();
        $user->analytics_enabled = (bool) ($validated['analytics_enabled'] ?? false);
        $user->save();

        $this->dispatch('analytics-settings-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-base-content">
            {{ __('Analytics') }}
        </h2>

        <p class="mt-1 text-sm opacity-70">
            {{ __('Enable analytics tracking for your account (unique daily views).') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-4">
        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="analytics_enabled" />
            <span class="text-sm">{{ __('Enable analytics') }}</span>
        </label>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="analytics-settings-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>

    @if (auth()->user()->analytics_enabled)
        <div class="pt-3">
            <a class="link link-hover" href="{{ route('analytics') }}" wire:navigate>Open Analytics</a>
        </div>
    @endif
</section>
