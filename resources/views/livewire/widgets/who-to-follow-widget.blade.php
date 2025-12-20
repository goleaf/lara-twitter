<div class="card bg-base-100 border">
    <div class="card-body">
        <div class="flex items-center justify-between">
            <div class="font-semibold">Who to follow</div>
            <a class="link link-primary text-sm" href="{{ route('explore') }}" wire:navigate>Explore</a>
        </div>

        @guest
            <div class="pt-3 text-sm text-base-content/70">
                Sign in to follow people you are interested in.
            </div>
        @endguest

        @auth
            <div class="space-y-1 pt-3">
                @forelse ($this->recommendedUsers as $u)
                    @php($mutualCount = (int) ($u->getAttribute('mutual_count') ?? 0))
                    @php($interestPostsCount = (int) ($u->getAttribute('interest_posts_count') ?? 0))

                    <x-list-row>
                        <a class="flex items-center gap-3 min-w-0 focus:outline-none" href="{{ route('profile.show', ['user' => $u]) }}" wire:navigate>
                            <div class="avatar shrink-0">
                                <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                    @if ($u->avatar_url)
                                        <img src="{{ $u->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                            {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="font-semibold truncate">
                                    {{ $u->name }}
                                    @if ($u->is_verified)
                                        <x-verified-icon class="ms-1 align-middle" />
                                    @endif
                                </div>
                                <div class="text-xs opacity-60 truncate">
                                    &#64;{{ $u->username }}
                                    ·
                                    @if ($mutualCount)
                                        {{ $mutualCount }} mutual
                                    @elseif ($interestPostsCount)
                                        {{ $interestPostsCount }} posts in your interests
                                    @else
                                        {{ $u->followers_count ?? 0 }} followers
                                    @endif
                                </div>
                            </div>
                        </a>

                        <button
                            type="button"
                            class="btn btn-primary btn-xs"
                            wire:click="toggleFollow({{ $u->id }})"
                            wire:loading.attr="disabled"
                            wire:target="toggleFollow({{ $u->id }})"
                        >
                            Follow
                        </button>
                    </x-list-row>
                @empty
                    <x-empty-state>
                        No recommendations yet.
                    </x-empty-state>
                @endforelse
            </div>
        @endauth
    </div>
</div>
