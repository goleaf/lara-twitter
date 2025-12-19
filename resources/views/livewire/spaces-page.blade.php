<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
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
                    <div class="alert alert-warning" role="alert">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm.75 6a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0V8ZM12 17a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd" />
                        </svg>
                        <div class="text-sm">
                            You need at least {{ $minFollowers }} followers to host a Space.
                            You currently have {{ $myFollowers }}.
                        </div>
                    </div>
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

                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="checkbox checkbox-sm" wire:model="recording_enabled" />
                        <span class="label-text">Enable recording metadata (30 days after end)</span>
                    </label>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
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
            <div class="font-semibold">Live now</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->liveSpaces as $space)
                    <a class="flex items-center justify-between gap-3 rounded-box px-3 py-2 hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('spaces.show', $space) }}" wire:navigate>
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
                    </a>
                @empty
                    <div class="opacity-70 text-sm">No live spaces.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Upcoming</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->upcomingSpaces as $space)
                    <a class="flex items-center justify-between gap-3 rounded-box px-3 py-2 hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('spaces.show', $space) }}" wire:navigate>
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
                    </a>
                @empty
                    <div class="opacity-70 text-sm">No upcoming spaces.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
