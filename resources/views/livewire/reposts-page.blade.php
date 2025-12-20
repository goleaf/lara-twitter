@php($primary = $this->primaryPost())
@php($isQuotes = $tab === 'quotes')
@php($rows = $isQuotes ? $this->quotes : $this->retweeters)

<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xl font-semibold">Reposts</div>
                    <div class="text-sm opacity-70">See who retweeted and quote tweeted this post.</div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="badge badge-outline badge-sm">{{ $rows->total() }}</span>
                    <a class="btn btn-ghost btn-sm" href="{{ route('posts.show', $primary) }}" wire:navigate>Back</a>
                </div>
            </div>

            <div class="tabs tabs-boxed mt-4">
                <button type="button" class="tab {{ $tab === 'retweets' ? 'tab-active' : '' }}" wire:click="$set('tab', 'retweets')">
                    Retweets
                </button>
                <button type="button" class="tab {{ $tab === 'quotes' ? 'tab-active' : '' }}" wire:click="$set('tab', 'quotes')">
                    Quotes
                </button>
            </div>
        </div>
    </div>

    @if ($tab === 'quotes')
        <div class="space-y-3">
            @forelse ($rows as $quote)
                <livewire:post-card :post="$quote" :key="'quote-'.$quote->id" />
            @empty
                <x-empty-state>
                    No quote tweets yet.
                </x-empty-state>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $rows->links() }}
        </div>
    @else
        <div class="card bg-base-100 border">
            <div class="card-body">
                    <div class="space-y-3">
                        @forelse ($rows as $retweet)
                            @php($user = $retweet->user)
                            @if (! $user)
                            @continue
                        @endif

                            <x-list-row href="{{ route('profile.show', ['user' => $user->username]) }}" wire:navigate>
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="avatar">
                                        <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                            @if ($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                        @else
                                            <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div class="font-semibold truncate">
                                        {{ $user->name }}
                                        @if ($user->is_verified)
                                            <x-verified-icon class="ms-1 align-middle" />
                                        @endif
                                    </div>
                                    <div class="text-xs opacity-60 truncate">&#64;{{ $user->username }}</div>
                                    </div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0">{{ $retweet->created_at->diffForHumans() }}</div>
                            </x-list-row>
                        @empty
                            <x-empty-state>
                                No retweets yet.
                            </x-empty-state>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="pt-2">
            {{ $rows->links() }}
        </div>
    @endif
</div>
