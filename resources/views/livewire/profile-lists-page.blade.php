<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xl font-semibold">Lists</div>
                    <div class="opacity-70 text-sm">&#64;{{ $user->username }}</div>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('profile.show', ['user' => $user]) }}" wire:navigate>Back</a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="space-y-2">
                @forelse ($this->lists as $list)
                    <a class="card bg-base-200 card-hover" href="{{ route('lists.show', $list) }}" wire:navigate>
                        <div class="card-body py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold truncate">{{ $list->name }}</div>
                                    <div class="text-sm opacity-70 truncate">by &#64;{{ $list->owner->username }}</div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0">{{ $list->members_count }} members</div>
                            </div>
                            @if ($list->description)
                                <div class="text-sm opacity-70 truncate">{{ $list->description }}</div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="opacity-70 text-sm">No public lists yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="pt-2">
        {{ $this->lists->links() }}
    </div>
</div>
