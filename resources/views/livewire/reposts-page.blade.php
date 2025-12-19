@php($primary = $this->primaryPost())

<div class="max-w-2xl mx-auto space-y-4">
    <div>
        <a class="link link-hover opacity-70" href="{{ route('posts.show', $primary) }}" wire:navigate>← Back to post</a>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Reposts</div>
            <div class="text-sm opacity-70">See who retweeted and quote tweeted this post.</div>

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
            @forelse ($this->quotes as $quote)
                <livewire:post-card :post="$quote" :key="'quote-'.$quote->id" />
            @empty
                <div class="opacity-70">No quote tweets yet.</div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $this->quotes->links() }}
        </div>
    @else
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="space-y-3">
                    @forelse ($this->retweeters as $retweet)
                        <a class="flex items-center justify-between link link-hover" href="{{ route('profile.show', ['user' => $retweet->user->username]) }}" wire:navigate>
                            <span>
                                <span class="font-semibold">{{ $retweet->user->name }}</span>
                                <span class="opacity-60 font-normal">&#64;{{ $retweet->user->username }}</span>
                            </span>
                            <span class="text-sm opacity-60">{{ $retweet->created_at->diffForHumans() }}</span>
                        </a>
                    @empty
                        <div class="opacity-70">No retweets yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="pt-2">
            {{ $this->retweeters->links() }}
        </div>
    @endif
</div>

