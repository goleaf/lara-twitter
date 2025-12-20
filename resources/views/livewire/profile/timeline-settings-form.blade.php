<?php

use App\Http\Requests\Profile\UpdateTimelineSettingsRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $show_replies = false;
    public bool $show_retweets = true;

    public function mount(): void
    {
        $settings = Auth::user()->timeline_settings ?? [];
        $this->show_replies = (bool) ($settings['show_replies'] ?? false);
        $this->show_retweets = (bool) ($settings['show_retweets'] ?? true);
    }

    public function save(): void
    {
        $validated = $this->validate(UpdateTimelineSettingsRequest::rulesFor());

        Auth::user()->update([
            'timeline_settings' => [
                'show_replies' => (bool) $validated['show_replies'],
                'show_retweets' => (bool) $validated['show_retweets'],
            ],
        ]);

        $this->dispatch('timeline-settings-updated');
    }
}; ?>

<section>
    <header class="space-y-1">
        <h2 class="text-xl font-semibold text-base-content">Timeline</h2>
        <p class="text-sm opacity-70">Customize what appears in your Following feed.</p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-4">
        <x-choice-card>
            <div class="min-w-0">
                <div class="font-medium">Show replies</div>
                <div class="text-sm opacity-70">Include replies from accounts you follow.</div>
            </div>
            <input type="checkbox" class="toggle toggle-sm mt-1" wire:model="show_replies" wire:loading.attr="disabled" wire:target="save" />
        </x-choice-card>
        <x-input-error class="mt-2" :messages="$errors->get('show_replies')" />

        <x-choice-card>
            <div class="min-w-0">
                <div class="font-medium">Show retweets</div>
                <div class="text-sm opacity-70">Include retweets from accounts you follow.</div>
            </div>
            <input type="checkbox" class="toggle toggle-sm mt-1" wire:model="show_retweets" wire:loading.attr="disabled" wire:target="save" />
        </x-choice-card>
        <x-input-error class="mt-2" :messages="$errors->get('show_retweets')" />

        <div class="flex items-center gap-3 pt-2">
            <x-primary-button wire:loading.attr="disabled" wire:target="save">Save</x-primary-button>
            <x-action-message class="me-3" on="timeline-settings-updated">Saved.</x-action-message>
        </div>
    </form>
</section>
