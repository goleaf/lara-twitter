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
    <header class="space-y-1">
        <h2 class="text-xl font-semibold text-base-content">
            {{ __('Analytics') }}
        </h2>

        <p class="text-sm opacity-70">
            {{ __('Enable analytics tracking for your account (unique daily views).') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-4">
        <label class="flex items-start justify-between gap-4 rounded-box border border-base-200 bg-base-200/40 px-4 py-3 cursor-pointer">
            <div class="min-w-0">
                <div class="font-medium">{{ __('Enable analytics') }}</div>
                <div class="text-sm opacity-70">{{ __('Track views and clicks as unique daily events (not total impressions).') }}</div>
            </div>
            <input type="checkbox" class="toggle toggle-sm mt-1" wire:model="analytics_enabled" />
        </label>

        <div class="flex items-center gap-3 pt-2">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="analytics-settings-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>

    @if (auth()->user()->analytics_enabled)
        <div class="pt-3">
            <a class="btn btn-ghost btn-sm" href="{{ route('analytics') }}" wire:navigate>Open Analytics</a>
        </div>
    @endif
</section>
