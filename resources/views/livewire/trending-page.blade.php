<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-4">
                <div class="text-xl font-semibold">Trending</div>
                @auth
                    @if (auth()->user()->location)
                        <div class="text-sm opacity-70">{{ auth()->user()->location }}</div>
                    @endif
                @endauth
            </div>

            <div class="tabs tabs-boxed mt-4">
                <a class="tab {{ $tab === 'hashtags' ? 'tab-active' : '' }}" href="{{ route('trending', ['tab' => 'hashtags']) }}" wire:navigate>Hashtags</a>
                <a class="tab {{ $tab === 'keywords' ? 'tab-active' : '' }}" href="{{ route('trending', ['tab' => 'keywords']) }}" wire:navigate>Keywords</a>
            </div>
        </div>
    </div>

    @if ($tab === 'keywords')
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Trending keywords (24h)</div>
                <div class="space-y-2 pt-2">
                    @forelse ($this->trendingKeywords as $row)
                        <a class="flex items-center justify-between hover:bg-base-200 rounded-box px-2 py-2" href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:navigate>
                            <div class="font-medium">{{ $row['keyword'] }}</div>
                            <div class="text-sm opacity-60">{{ $row['count'] }}</div>
                        </a>
                    @empty
                        <div class="opacity-70 text-sm">No keywords yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Trending hashtags (24h)</div>
                <div class="flex flex-wrap gap-2 pt-2">
                    @forelse ($this->trendingHashtags as $tag)
                        <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
                            #{{ $tag->tag }}
                            <span class="opacity-60 ms-1">{{ $tag->uses_count }}</span>
                        </a>
                    @empty
                        <div class="opacity-70 text-sm">No hashtags yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

