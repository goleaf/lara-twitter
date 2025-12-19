<div class="max-w-2xl mx-auto space-y-4">
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
                    <div class="alert alert-warning">
                        <div class="text-sm">
                            You need at least {{ $minFollowers }} followers to host a Space.
                            You currently have {{ $myFollowers }}.
                        </div>
                    </div>
                @endif

                <form wire:submit="create" class="space-y-3">
                    <div>
                        <x-input-label for="title" value="Title" />
                        <x-text-input id="title" class="mt-1 block w-full" wire:model="title" />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" class="textarea textarea-bordered mt-1 block w-full" rows="3" wire:model="description"></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div>
                        <x-input-label for="scheduled_for" value="Schedule (optional)" />
                        <input id="scheduled_for" type="datetime-local" class="input input-bordered w-full mt-1" wire:model="scheduled_for" />
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
                    <a class="card bg-base-200 border hover:border-base-300 transition" href="{{ route('spaces.show', $space) }}" wire:navigate>
                        <div class="card-body py-4">
                            <div class="font-semibold">{{ $space->title }}</div>
                            <div class="text-sm opacity-70">Host: &#64;{{ $space->host->username }}</div>
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
                    <a class="card bg-base-200 border hover:border-base-300 transition" href="{{ route('spaces.show', $space) }}" wire:navigate>
                        <div class="card-body py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold truncate">{{ $space->title }}</div>
                                    <div class="text-sm opacity-70 truncate">Host: &#64;{{ $space->host->username }}</div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0">
                                    {{ $space->scheduled_for?->diffForHumans() ?? 'Unscheduled' }}
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="opacity-70 text-sm">No upcoming spaces.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
