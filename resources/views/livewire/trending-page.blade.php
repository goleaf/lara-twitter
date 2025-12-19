<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-4">
                <div class="text-xl font-semibold">Trending</div>
                <div class="flex items-center gap-2">
                    <input
                        class="input input-bordered input-sm w-48"
                        type="text"
                        placeholder="Location (optional)"
                        wire:model.live.debounce.400ms="loc"
                    />
                    <a class="btn btn-ghost btn-sm" href="{{ route('trending', ['tab' => $tab, 'loc' => '']) }}" wire:navigate>Global</a>
                </div>
            </div>

            <div class="tabs tabs-boxed mt-4">
                <a class="tab {{ $tab === 'hashtags' ? 'tab-active' : '' }}" href="{{ route('trending', ['tab' => 'hashtags', 'loc' => $loc]) }}" wire:navigate>Hashtags</a>
                <a class="tab {{ $tab === 'keywords' ? 'tab-active' : '' }}" href="{{ route('trending', ['tab' => 'keywords', 'loc' => $loc]) }}" wire:navigate>Keywords</a>
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
                        <div class="flex items-center gap-1">
                            <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
                                #{{ $tag->tag }}
                                <span class="opacity-60 ms-1">{{ $tag->uses_count }}</span>
                            </a>
                            <livewire:report-button :reportable-type="\App\Models\Hashtag::class" :reportable-id="$tag->id" label="Report" :key="'report-tag-'.$tag->id" />
                        </div>
                    @empty
                        <div class="opacity-70 text-sm">No hashtags yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
