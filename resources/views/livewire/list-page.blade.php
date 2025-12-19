<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <a class="avatar shrink-0" href="{{ route('profile.show', ['user' => $list->owner]) }}" wire:navigate>
                        <div class="w-12 rounded-full border border-base-200 bg-base-100">
                            @if ($list->owner->avatar_url)
                                <img src="{{ $list->owner->avatar_url }}" alt="" />
                            @else
                                <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                    {{ mb_strtoupper(mb_substr($list->owner->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="min-w-0">
                        <div class="flex items-start gap-2 flex-wrap">
                            <div class="text-xl font-semibold truncate">{{ $list->name }}</div>
                            @if ($list->is_private)
                                <span class="badge badge-outline badge-sm">Private</span>
                            @endif
                        </div>

                        <div class="text-sm opacity-70 truncate">
                            by <a class="link link-hover" href="{{ route('profile.show', ['user' => $list->owner]) }}" wire:navigate>&#64;{{ $list->owner->username }}</a>
                            · {{ $list->members_count }} members
                            · {{ $list->subscribers_count ?? 0 }} subscribers
                        </div>

                        @if ($list->description)
                            <div class="pt-2 text-sm opacity-80">{{ $list->description }}</div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <livewire:report-button :reportable-type="\App\Models\UserList::class" :reportable-id="$list->id" label="Report" :key="'report-list-'.$list->id" />
                    <a class="btn btn-ghost btn-sm" href="{{ route('lists.index') }}" wire:navigate>Back</a>
                </div>
            </div>

            @auth
                @if (! $list->is_private && auth()->id() !== $list->owner_id)
                    <div>
                        @php($isSubscribed = $this->isSubscribed())
                        <button class="btn btn-sm {{ $isSubscribed ? 'btn-outline' : 'btn-primary' }}" wire:click="toggleSubscribe">
                            {{ $isSubscribed ? 'Unsubscribe' : 'Subscribe' }}
                        </button>
                    </div>
                @endif
            @endauth

            <div class="flex flex-wrap gap-2">
                @foreach ($this->members as $member)
                    <a class="badge badge-outline badge-sm" href="{{ route('profile.show', ['user' => $member]) }}" wire:navigate>
                        &#64;{{ $member->username }}
                    </a>
                @endforeach
            </div>

            @auth
                @if (auth()->id() === $list->owner_id)
                    <div class="divider">Manage members</div>

                    <form wire:submit="addMember" class="flex flex-col sm:flex-row gap-2">
                        <input class="input input-bordered input-sm w-full" placeholder="@username" wire:model="member_username" />
                        <button type="submit" class="btn btn-primary btn-sm shrink-0">Add</button>
                    </form>
                    <x-input-error class="mt-2" :messages="$errors->get('member_username')" />

                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach ($this->members as $member)
                            <button type="button" class="badge badge-sm badge-neutral" wire:click="removeMember({{ $member->id }})">
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
