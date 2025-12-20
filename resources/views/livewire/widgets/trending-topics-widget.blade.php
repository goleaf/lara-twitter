<div class="card bg-base-100 border widget-card">
    <span
        class="absolute top-0 left-0 w-1 h-1 opacity-0"
        aria-hidden="true"
        wire:poll.visible.180s
        data-livewire-poll-pausable
    ></span>
    <div class="card-body gap-4">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <div class="widget-kicker">Trending now</div>
                <div class="widget-title">Trending</div>
            </div>
            <a class="link link-primary text-sm" href="{{ route('trending') }}" wire:navigate>View all</a>
        </div>

        <div class="flex items-center justify-between text-[0.7rem] uppercase tracking-[0.18em] text-base-content/50">
            <span>Hashtags</span>
            <span class="badge badge-ghost badge-sm">Last 24h</span>
        </div>

        <div class="space-y-2 pt-2">
            @forelse ($this->trendingHashtags as $tag)
                <x-list-row href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:key="trending-widget-hashtag-{{ $tag->id }}" wire:navigate>
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="text-xs font-semibold text-base-content/40 w-6 text-right tabular-nums">{{ $loop->iteration }}</div>
                        <div class="font-medium min-w-0 truncate">#{{ $tag->tag }}</div>
                    </div>
                    <div class="text-xs opacity-60 tabular-nums">{{ $tag->uses_count }}</div>
                </x-list-row>
            @empty
                <x-empty-state>
                    No hashtags yet.
                </x-empty-state>
            @endforelse
        </div>

        <div class="divider my-1"></div>

        <div class="flex items-center justify-between text-[0.7rem] uppercase tracking-[0.18em] text-base-content/50">
            <span>Keywords</span>
            <span class="badge badge-ghost badge-sm">Last 24h</span>
        </div>

        <div class="space-y-2 pt-2">
            @forelse ($this->trendingKeywords as $row)
                <x-list-row href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:key="trending-widget-keyword-{{ $row['keyword'] }}" wire:navigate>
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="text-xs font-semibold text-base-content/40 w-6 text-right tabular-nums">{{ $loop->iteration }}</div>
                        <div class="font-medium min-w-0 truncate">{{ $row['keyword'] }}</div>
                    </div>
                    <div class="text-xs opacity-60 tabular-nums">{{ $row['count'] }}</div>
                </x-list-row>
            @empty
                <x-empty-state>
                    No keywords yet.
                </x-empty-state>
            @endforelse
        </div>
    </div>
</div>
