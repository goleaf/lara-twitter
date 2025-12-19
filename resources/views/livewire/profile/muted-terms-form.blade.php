<?php

use App\Http\Requests\Profile\DeleteMutedTermRequest;
use App\Http\Requests\Profile\StoreMutedTermRequest;
use App\Models\MutedTerm;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $term = '';
    public string $duration = 'forever';

    public bool $whole_word = false;
    public bool $only_non_followed = false;
    public bool $mute_timeline = true;
    public bool $mute_notifications = true;

    public function add(): void
    {
        $validated = $this->validate(StoreMutedTermRequest::rulesFor());

        $term = trim((string) ($validated['term'] ?? ''));
        if ($term === '') {
            $this->addError('term', 'Term is required.');
            return;
        }

        $expiresAt = null;
        $duration = (string) ($validated['duration'] ?? 'forever');
        if ($duration === '1h') {
            $expiresAt = now()->addHour();
        } elseif ($duration === '1d') {
            $expiresAt = now()->addDay();
        } elseif ($duration === '7d') {
            $expiresAt = now()->addDays(7);
        } elseif ($duration === '30d') {
            $expiresAt = now()->addDays(30);
        }

        MutedTerm::query()->create([
            'user_id' => Auth::id(),
            'term' => mb_substr($term, 0, 100),
            'whole_word' => (bool) ($validated['whole_word'] ?? false),
            'only_non_followed' => (bool) ($validated['only_non_followed'] ?? false),
            'mute_timeline' => (bool) ($validated['mute_timeline'] ?? true),
            'mute_notifications' => (bool) ($validated['mute_notifications'] ?? true),
            'expires_at' => $expiresAt,
        ]);

        $this->reset(['term']);
        $this->dispatch('muted-terms-updated');
    }

    public function remove(int $id): void
    {
        $this->validate(DeleteMutedTermRequest::rulesFor(), ['id' => $id]);

        MutedTerm::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        $this->dispatch('muted-terms-updated');
    }

    public function getMutedTermsProperty()
    {
        return MutedTerm::query()
            ->where('user_id', Auth::id())
            ->orderByRaw('case when expires_at is null then 0 else 1 end')
            ->orderBy('expires_at')
            ->latest()
            ->limit(50)
            ->get();
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-base-content">
            {{ __('Muted words') }}
        </h2>

        <p class="mt-1 text-sm opacity-70">
            {{ __('Hide posts containing specific words, phrases, or #hashtags. Applies to your timeline and (optionally) notifications.') }}
        </p>
    </header>

    <form wire:submit="add" class="mt-6 space-y-4">
        <div>
            <x-input-label for="term" :value="__('Word, phrase, or hashtag')" />
            <x-text-input
                wire:model="term"
                id="term"
                name="term"
                type="text"
                class="mt-1 block w-full"
                placeholder="spoiler, \"movie name\", #politics"
                autocomplete="off"
            />
            <x-input-error class="mt-2" :messages="$errors->get('term')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div>
                <x-input-label for="duration" :value="__('Duration')" />
                <select id="duration" class="select select-bordered w-full mt-1" wire:model="duration">
                    <option value="forever">{{ __('Forever') }}</option>
                    <option value="1h">{{ __('1 hour') }}</option>
                    <option value="1d">{{ __('1 day') }}</option>
                    <option value="7d">{{ __('7 days') }}</option>
                    <option value="30d">{{ __('30 days') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('duration')" />
            </div>
        </div>

        <div class="divider my-2"></div>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="mute_timeline" />
            <span class="text-sm">{{ __('Mute in timeline') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="mute_notifications" />
            <span class="text-sm">{{ __('Mute in notifications') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="whole_word" />
            <span class="text-sm">{{ __('Whole word (best effort)') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="only_non_followed" />
            <span class="text-sm">{{ __('Only apply to people you do not follow') }}</span>
        </label>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Add') }}</x-primary-button>

            <x-action-message class="me-3" on="muted-terms-updated">
                {{ __('Updated.') }}
            </x-action-message>
        </div>
    </form>

    <div class="divider my-4"></div>

    <div class="space-y-2">
        @forelse ($this->mutedTerms as $row)
            <div class="flex items-start justify-between gap-3 border rounded-box p-3 bg-base-100">
                <div class="min-w-0">
                    <div class="font-semibold truncate">{{ $row->term }}</div>
                    <div class="text-xs opacity-70">
                        @if ($row->expires_at)
                            {{ __('Expires') }} {{ $row->expires_at->diffForHumans() }}
                        @else
                            {{ __('Forever') }}
                        @endif
                        · {{ $row->mute_timeline ? __('Timeline') : __('No timeline') }}
                        · {{ $row->mute_notifications ? __('Notifications') : __('No notifications') }}
                        @if ($row->only_non_followed)
                            · {{ __('Non-followed only') }}
                        @endif
                        @if ($row->whole_word)
                            · {{ __('Whole word') }}
                        @endif
                    </div>
                </div>

                <button type="button" class="btn btn-ghost btn-xs" wire:click="remove({{ $row->id }})">
                    {{ __('Remove') }}
                </button>
            </div>
        @empty
            <div class="opacity-70 text-sm">{{ __('No muted terms yet.') }}</div>
        @endforelse
    </div>
</section>
