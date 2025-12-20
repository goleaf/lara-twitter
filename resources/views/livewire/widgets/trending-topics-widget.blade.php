<div class="card bg-base-100 border" wire:poll.visible.180s>
    <div class="card-body">
        <div class="flex items-center justify-between">
            <div class="font-semibold">Trending</div>
            <a class="link link-primary text-sm" href="{{ route('trending') }}" wire:navigate>View all</a>
        </div>

        <div class="space-y-2 pt-3">
            @forelse ($this->trendingHashtags as $tag)
                <x-list-row href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
                    <div class="font-medium min-w-0 truncate">#{{ $tag->tag }}</div>
                    <div class="text-sm opacity-60">{{ $tag->uses_count }}</div>
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
                <x-list-row href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:navigate>
                    <div class="font-medium min-w-0 truncate">{{ $row['keyword'] }}</div>
                    <div class="text-sm opacity-60">{{ $row['count'] }}</div>
                </x-list-row>
            @empty
                <x-empty-state>
                    No keywords yet.
                </x-empty-state>
            @endforelse
        </div>
    </div>
</div>
