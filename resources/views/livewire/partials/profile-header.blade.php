<div class="card bg-base-100 border overflow-hidden">
    @if ($user->header_url)
        <div class="h-32 sm:h-40 bg-cover bg-center bg-base-200" style="background-image: url('{{ $user->header_url }}')">
            <div class="h-full w-full bg-gradient-to-t from-base-100/90 via-base-100/20 to-transparent"></div>
        </div>
    @else
        <div class="h-32 sm:h-40 bg-gradient-to-r from-primary/15 via-accent/10 to-secondary/10"></div>
    @endif

    <div class="card-body">
        <div class="-mt-12 flex items-end justify-between gap-4">
            <div class="flex items-end gap-3">
                <div class="avatar">
                    <div class="w-20 rounded-full border border-base-200 bg-base-100 ring-4 ring-base-100 shadow-sm">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="" loading="lazy" decoding="async" />
                        @else
                            <div class="bg-base-200 grid place-items-center h-full w-full text-xl font-semibold">
                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="text-xl font-bold">
                        {{ $user->name }}
                        @if ($user->is_verified)
                            <x-verified-icon class="ms-1 align-middle" />
                        @endif
                    </div>
                    <div class="opacity-60">&#64;{{ $user->username }}</div>
                </div>
            </div>

            @auth
                @if (auth()->id() !== $user->id)
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <a class="btn btn-outline btn-sm" href="{{ route('messages.new', ['user' => $user]) }}" wire:navigate>Message</a>
                        <button wire:click="toggleFollow" wire:loading.attr="disabled" wire:target="toggleFollow" class="btn btn-sm {{ $this->isFollowing ? 'btn-outline' : 'btn-primary' }}">
                            {{ $this->isFollowing ? 'Unfollow' : 'Follow' }}
                        </button>

                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-sm">More</div>
                            <ul tabindex="0" class="dropdown-content z-[1] menu bg-base-100 border border-base-200 rounded-box shadow-lg mt-2 w-52 p-2">
                                <li>
                                    <livewire:report-button
                                        :reportable-type="\App\Models\User::class"
                                        :reportable-id="$user->id"
                                        label="Report"
                                        button-class="btn btn-ghost btn-sm justify-start w-full"
                                        :show-notice="false"
                                        :key="'report-user-'.$user->id"
                                    />
                                </li>
                                <li>
                                    <button type="button" wire:click="toggleMute" wire:loading.attr="disabled" wire:target="toggleMute" class="btn btn-ghost btn-sm justify-start w-full">
                                        {{ $this->isMuted ? 'Unmute' : 'Mute' }}
                                    </button>
                                </li>
                                <li>
                                    <button type="button" wire:click="toggleBlock" wire:loading.attr="disabled" wire:target="toggleBlock" class="btn btn-ghost btn-sm justify-start w-full text-error">
                                        {{ $this->hasBlocked ? 'Unblock' : 'Block' }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                @else
                    <a class="btn btn-outline btn-sm" href="{{ route('profile') }}" wire:navigate>Edit profile</a>
                @endif
            @endauth
        </div>

        @if ($user->bio)
            <div class="pt-3 text-sm leading-relaxed">{{ $user->bio }}</div>
        @endif

        <div class="pt-4 flex flex-wrap gap-2">
            @if ($user->location)
                <span class="badge badge-ghost badge-sm">{{ $user->location }}</span>
            @endif

            @if ($user->website)
                <a class="badge badge-outline badge-sm" href="{{ $user->website }}" target="_blank" rel="noreferrer">
                    {{ preg_replace('#^https?://#', '', $user->website) }}
                </a>
            @endif

            <span class="badge badge-ghost badge-sm">Joined {{ $user->created_at->format('M Y') }}</span>

            @if ($user->birth_date && $user->canShowBirthDateTo(auth()->user()))
                <span class="badge badge-ghost badge-sm">Born {{ $user->birth_date->format('M j, Y') }}</span>
            @endif
        </div>

        <div class="pt-4 grid grid-cols-3 gap-2">
            <a
                class="rounded-box border border-base-200 bg-base-200/40 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20"
                href="{{ route('profile.followers', ['user' => $user]) }}"
                wire:navigate
            >
                <div class="font-semibold leading-none">{{ $user->followers_count }}</div>
                <div class="text-xs opacity-70">followers</div>
            </a>
            <a
                class="rounded-box border border-base-200 bg-base-200/40 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20"
                href="{{ route('profile.following', ['user' => $user]) }}"
                wire:navigate
            >
                <div class="font-semibold leading-none">{{ $user->following_count }}</div>
                <div class="text-xs opacity-70">following</div>
            </a>
            <a
                class="rounded-box border border-base-200 bg-base-200/40 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20"
                href="{{ route('profile.lists', ['user' => $user]) }}"
                wire:navigate
            >
                <div class="font-semibold leading-none">{{ $this->listsCount }}</div>
                <div class="text-xs opacity-70">lists</div>
            </a>
        </div>

        <div class="tabs tabs-boxed mt-4">
            <a class="tab {{ $active === 'posts' ? 'tab-active' : '' }}" href="{{ route('profile.show', ['user' => $user]) }}" wire:navigate>Posts</a>
            <a class="tab {{ $active === 'likes' ? 'tab-active' : '' }}" href="{{ route('profile.likes', ['user' => $user]) }}" wire:navigate>Likes</a>
            <a class="tab {{ $active === 'replies' ? 'tab-active' : '' }}" href="{{ route('profile.replies', ['user' => $user]) }}" wire:navigate>Replies</a>
            <a class="tab {{ $active === 'media' ? 'tab-active' : '' }}" href="{{ route('profile.media', ['user' => $user]) }}" wire:navigate>Media</a>
        </div>
    </div>
</div>
