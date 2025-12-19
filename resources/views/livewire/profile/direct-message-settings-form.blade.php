<?php

use App\Http\Requests\Profile\UpdateDirectMessageSettingsRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $dm_policy = 'everyone';
    public bool $dm_allow_requests = true;
    public bool $dm_read_receipts = true;

    public function mount(): void
    {
        $this->dm_policy = Auth::user()->dm_policy ?? 'everyone';
        $this->dm_allow_requests = (bool) (Auth::user()->dm_allow_requests ?? true);
        $this->dm_read_receipts = (bool) (Auth::user()->dm_read_receipts ?? true);
    }

    public function save(): void
    {
        $validated = $this->validate(UpdateDirectMessageSettingsRequest::rulesFor());

        Auth::user()->update($validated);

        $this->dispatch('dm-settings-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-base-content">Direct Messages</h2>
        <p class="mt-1 text-sm opacity-70">Control who can message you and whether message requests are allowed.</p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-6">
        <div>
            <x-input-label for="dm_policy" value="Who can message you?" />
            <select wire:model="dm_policy" id="dm_policy" class="select select-bordered w-full">
                <option value="everyone">Everyone</option>
                <option value="following">Only people you follow</option>
                <option value="none">No one</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('dm_policy')" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="font-medium">Allow message requests</div>
                <div class="text-sm opacity-70">If disabled, users who don't meet your policy cannot start a new DM.</div>
            </div>
            <input type="checkbox" class="toggle" wire:model="dm_allow_requests" />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('dm_allow_requests')" />

        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="font-medium">Read receipts</div>
                <div class="text-sm opacity-70">If disabled, others won’t see when you’ve read their messages.</div>
            </div>
            <input type="checkbox" class="toggle" wire:model="dm_read_receipts" />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('dm_read_receipts')" />

        <div class="flex items-center gap-4">
            <x-primary-button>Save</x-primary-button>
            <x-action-message class="me-3" on="dm-settings-updated">Saved.</x-action-message>
        </div>
    </form>
</section>
