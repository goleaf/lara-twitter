<?php

use App\Http\Requests\Profile\UpdateInterestHashtagsRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $interest_hashtags = '';

    public function mount(): void
    {
        $tags = Auth::user()->interest_hashtags ?? [];
        $this->interest_hashtags = collect($tags)
            ->map(fn ($t) => '#'.ltrim((string) $t, '#'))
            ->join(', ');
    }

    public function save(): void
    {
        $validated = $this->validate(UpdateInterestHashtagsRequest::rulesFor());

        $raw = (string) ($validated['interest_hashtags'] ?? '');

        $tags = collect(preg_split('/[\\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->map(fn ($t) => mb_strtolower(ltrim($t, '#')))
            ->filter(fn ($t) => $t !== '' && preg_match('/^[\\pL\\pN][\\pL\\pN_]{0,49}$/u', $t))
            ->unique()
            ->take(20)
            ->values()
            ->all();

        $user = Auth::user();
        $user->interest_hashtags = $tags;
        $user->save();

        $this->dispatch('interest-hashtags-updated');
    }
}; ?>

<section>
    <header class="space-y-1">
        <h2 class="text-xl font-semibold text-base-content">
            {{ __('Interests') }}
        </h2>

        <p class="text-sm opacity-70">
            {{ __('Used to prioritize trends and discovery (hashtags only).') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div>
            <x-input-label for="interest_hashtags" :value="__('Interest hashtags')" />
            <x-text-input
                wire:model="interest_hashtags"
                id="interest_hashtags"
                name="interest_hashtags"
                type="text"
                class="mt-1 block w-full input-sm"
                placeholder="#laravel, #php, #livewire"
                autocomplete="off"
            />
            <x-input-error class="mt-2" :messages="$errors->get('interest_hashtags')" />
            <div class="mt-1 text-xs opacity-70">Up to 20 tags. Letters/numbers/underscore only.</div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="interest-hashtags-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
