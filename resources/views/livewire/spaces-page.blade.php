<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @php($liveSpaces = $this->liveSpaces)
    @php($upcomingSpaces = $this->upcomingSpaces)

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-4">
                <div class="text-xl font-semibold">Spaces</div>
            </div>
            <div class="text-sm opacity-70 pt-1">
                MVP: scheduling + join/leave + roles. Live audio is not implemented yet.
            </div>
        </div>
    </div>

    @auth
        <div class="card bg-base-100 border">
            <div class="card-body space-y-3">
                <div class="font-semibold">Host a Space</div>
                <x-input-error class="mt-2" :messages="$errors->get('host')" />

                @php($minFollowers = \App\Models\Space::minFollowersToHost())
                @php($myFollowers = $minFollowers > 0 ? auth()->user()->followers()->count() : 0)

                @if ($minFollowers > 0 && $myFollowers < $minFollowers)
                    <x-callout type="warning" title="Host requirement">
                        You need at least <span class="font-semibold tabular-nums">{{ $minFollowers }}</span> followers to host a Space.
                        You currently have <span class="font-semibold tabular-nums">{{ $myFollowers }}</span>.
                    </x-callout>
                @endif

                <form wire:submit="create" class="space-y-3">
                    <div>
                        <x-input-label for="title" value="Title" />
                        <x-text-input id="title" class="mt-1 block w-full input-sm" wire:model="title" />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" class="textarea textarea-bordered textarea-sm mt-1 block w-full" rows="3" wire:model="description"></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div>
                        <x-input-label for="scheduled_for" value="Schedule (optional)" />
                        <input id="scheduled_for" type="datetime-local" class="input input-bordered input-sm w-full mt-1" wire:model="scheduled_for" />
                        <x-input-error class="mt-2" :messages="$errors->get('scheduled_for')" />
                    </div>

                    <x-choice-card>
                        <span class="text-sm font-medium">Enable recording metadata (30 days after end)</span>
                        <input type="checkbox" class="toggle toggle-sm mt-1" wire:model="recording_enabled" wire:loading.attr="disabled" wire:target="create" />
                    </x-choice-card>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
                            wire:loading.attr="disabled"
                            wire:target="create"
                            @disabled($minFollowers > 0 && $myFollowers < $minFollowers)
                        >
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endauth

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-3">
                <div class="font-semibold">Live now</div>
                <span class="badge badge-outline badge-sm">{{ $liveSpaces->count() }}</span>
            </div>
                <div class="space-y-2 pt-2">
                    @forelse ($liveSpaces as $space)
                        <x-list-row href="{{ route('spaces.show', $space) }}" wire:navigate wire:key="live-space-row-{{ $space->id }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="avatar shrink-0">
                                    <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                        @if ($space->host->avatar_url)
                                        <img src="{{ $space->host->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                            {{ mb_strtoupper(mb_substr($space->host->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="font-semibold truncate">{{ $space->title }}</div>
                                <div class="text-xs opacity-60 truncate">Host: &#64;{{ $space->host->username }}</div>
                            </div>
                        </div>

                        <div class="shrink-0 text-right space-y-1">
                            <span class="badge badge-primary badge-sm">Live</span>
                            @if ($space->started_at)
                                <div class="text-xs opacity-60">{{ $space->started_at->diffForHumans() }}</div>
                                @endif
                            </div>
                        </x-list-row>
                    @empty
                        <x-empty-state>
                            No live spaces.
                        </x-empty-state>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-3">
                <div class="font-semibold">Upcoming</div>
                <span class="badge badge-outline badge-sm">{{ $upcomingSpaces->count() }}</span>
            </div>
                <div class="space-y-2 pt-2">
                    @forelse ($upcomingSpaces as $space)
                        <x-list-row href="{{ route('spaces.show', $space) }}" wire:navigate wire:key="upcoming-space-row-{{ $space->id }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="avatar shrink-0">
                                    <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                        @if ($space->host->avatar_url)
                                        <img src="{{ $space->host->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                            {{ mb_strtoupper(mb_substr($space->host->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="font-semibold truncate">{{ $space->title }}</div>
                                <div class="text-xs opacity-60 truncate">Host: &#64;{{ $space->host->username }}</div>
                            </div>
                        </div>

                        <div class="shrink-0 text-right space-y-1">
                            @if ($space->scheduled_for)
                                <span class="badge badge-outline badge-sm">Scheduled</span>
                                <div class="text-xs opacity-60">{{ $space->scheduled_for->diffForHumans() }}</div>
                            @else
                                    <span class="badge badge-ghost badge-sm">Unscheduled</span>
                                @endif
                            </div>
                        </x-list-row>
                    @empty
                        <x-empty-state>
                            No upcoming spaces.
                        </x-empty-state>
                @endforelse
            </div>
        </div>
    </div>
</div>
