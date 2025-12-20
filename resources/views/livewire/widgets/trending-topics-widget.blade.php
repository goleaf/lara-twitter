<div class="card bg-base-100 border" wire:poll.visible.180s>
    <div class="card-body">
        <div class="flex items-center justify-between">
            <div class="font-semibold">Trending</div>
            <a class="link link-primary text-sm" href="{{ route('trending') }}" wire:navigate>View all</a>
        </div>

        <div class="space-y-2 pt-3">
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

        <div class="divider my-2"></div>

        <div class="space-y-2">
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
