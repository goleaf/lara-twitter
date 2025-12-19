<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xl font-semibold truncate">{{ $list->name }}</div>
                    <div class="text-sm opacity-70">
                        by &#64;{{ $list->owner->username }}
                        · {{ $list->members_count }} members
                        · {{ $list->subscribers_count ?? 0 }} subscribers
                        {{ $list->is_private ? ' · Private' : '' }}
                    </div>
                    @if ($list->description)
                        <div class="pt-2">{{ $list->description }}</div>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <livewire:report-button :reportable-type="\App\Models\UserList::class" :reportable-id="$list->id" label="Report" :key="'report-list-'.$list->id" />
                    <a class="btn btn-ghost btn-sm" href="{{ route('lists.index') }}" wire:navigate>Back</a>
                </div>
            </div>

            @auth
                @if (! $list->is_private && auth()->id() !== $list->owner_id)
                    <div>
                        <button class="btn btn-outline btn-sm" wire:click="toggleSubscribe">
                            {{ $this->isSubscribed() ? 'Unsubscribe' : 'Subscribe' }}
                        </button>
                    </div>
                @endif
            @endauth

            <div class="flex flex-wrap gap-2">
                @foreach ($this->members as $member)
                    <a class="badge badge-outline" href="{{ route('profile.show', ['user' => $member]) }}" wire:navigate>
                        &#64;{{ $member->username }}
                    </a>
                @endforeach
            </div>

            @auth
                @if (auth()->id() === $list->owner_id)
                    <div class="divider">Manage members</div>

                    <form wire:submit="addMember" class="flex flex-col sm:flex-row gap-2">
                        <input class="input input-bordered w-full" placeholder="@username" wire:model="member_username" />
                        <button type="submit" class="btn btn-primary btn-sm shrink-0">Add</button>
                    </form>
                    <x-input-error class="mt-2" :messages="$errors->get('member_username')" />

                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach ($this->members as $member)
                            <button type="button" class="badge badge-neutral" wire:click="removeMember({{ $member->id }})">
                                Remove &#64;{{ $member->username }}
                            </button>
                        @endforeach
                    </div>
                @endif
            @endauth
        </div>
    </div>

    <div class="space-y-3">
        @foreach ($this->posts as $post)
            <livewire:post-card :post="$post" :key="$post->id" />
        @endforeach
    </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
