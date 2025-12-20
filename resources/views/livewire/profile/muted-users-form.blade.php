<?php

use App\Http\Requests\Profile\UnmuteUserRequest;
use App\Models\Mute;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public int $muted_id = 0;

    public function unmute(int $userId): void
    {
        $this->muted_id = $userId;
        $this->validate(UnmuteUserRequest::rulesFor());

        Mute::query()
            ->where('muter_id', Auth::id())
            ->where('muted_id', $userId)
            ->delete();

        $this->reset('muted_id');
        $this->dispatch('muted-users-updated');
    }

    public function getMutesProperty()
    {
        return Mute::query()
            ->where('muter_id', Auth::id())
            ->with('muted')
            ->latest()
            ->limit(200)
            ->get();
    }
}; ?>

<section>
    <header class="space-y-1">
        <h2 class="text-xl font-semibold text-base-content">
            {{ __('Muted accounts') }}
        </h2>

        <p class="text-sm opacity-70">
            {{ __('Muted accounts can still follow and message you, but you will not see their posts in your timeline or post-related notifications.') }}
        </p>
    </header>

    <div class="mt-6 space-y-2">
        @forelse ($this->mutes as $mute)
            @php($user = $mute->muted)
            @if (! $user)
                @continue
            @endif

            <div class="flex items-center justify-between gap-3 border border-base-200 rounded-box p-3 bg-base-200/40">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="avatar">
                        <div class="w-10 rounded-full border border-base-200 bg-base-100">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="" loading="lazy" decoding="async" />
                            @else
                                <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $user->name }}</div>
                        <div class="text-sm opacity-70 truncate">&#64;{{ $user->username }}</div>
                        <div class="text-xs opacity-60">
                            {{ __('Muted') }} {{ $mute->created_at?->diffForHumans() }}
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn btn-outline btn-sm"
                    wire:click="unmute({{ $user->id }})"
                    wire:loading.attr="disabled"
                    wire:target="unmute({{ $user->id }})"
                >
                    {{ __('Unmute') }}
                </button>
            </div>
        @empty
            <x-empty-state>
                {{ __('No muted accounts.') }}
            </x-empty-state>
        @endforelse
    </div>

    <div class="mt-4">
        <x-action-message class="me-3" on="muted-users-updated">
            {{ __('Updated.') }}
        </x-action-message>
    </div>
</section>
