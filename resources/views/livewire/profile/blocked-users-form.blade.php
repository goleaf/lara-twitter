<?php

use App\Http\Requests\Profile\UnblockUserRequest;
use App\Models\Block;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public int $blocked_id = 0;

    public function unblock(int $userId): void
    {
        $this->blocked_id = $userId;
        $this->validate(UnblockUserRequest::rulesFor());

        Block::query()
            ->where('blocker_id', Auth::id())
            ->where('blocked_id', $userId)
            ->delete();

        $this->reset('blocked_id');
        $this->dispatch('blocked-users-updated');
    }

    public function getBlocksProperty()
    {
        return Block::query()
            ->where('blocker_id', Auth::id())
            ->with('blocked')
            ->latest()
            ->limit(200)
            ->get();
    }
}; ?>

<section>
    <header class="space-y-1">
        <h2 class="text-xl font-semibold text-base-content">
            {{ __('Blocked accounts') }}
        </h2>

        <p class="text-sm opacity-70">
            {{ __('Blocked accounts cannot follow or message you, and you will not see their posts.') }}
        </p>
    </header>

    <div class="mt-6 space-y-2">
        @forelse ($this->blocks as $block)
            @php($user = $block->blocked)
            @if (! $user)
                @continue
            @endif

            <div class="flex items-center justify-between gap-3 border border-base-200 rounded-box p-3 bg-base-200/40" wire:key="blocked-user-{{ $block->id }}">
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
                            {{ __('Blocked') }} {{ $block->created_at?->diffForHumans() }}
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn btn-outline btn-sm"
                    wire:click="unblock({{ $user->id }})"
                    wire:loading.attr="disabled"
                    wire:target="unblock({{ $user->id }})"
                >
                    {{ __('Unblock') }}
                </button>
            </div>
        @empty
            <x-empty-state>
                {{ __('No blocked accounts.') }}
            </x-empty-state>
        @endforelse
    </div>

    <div class="mt-4">
        <x-action-message class="me-3" on="blocked-users-updated">
            {{ __('Updated.') }}
        </x-action-message>
    </div>
</section>
