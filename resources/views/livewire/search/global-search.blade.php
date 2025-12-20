<div class="relative">
    <label class="input input-bordered input-sm w-full flex items-center gap-2 bg-base-100/70 search-input">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 10.5 18.5a7.5 7.5 0 0 0 6.15-3.85Z" />
        </svg>
        <input
            type="search"
            wire:model.debounce.300ms="query"
            class="grow"
            placeholder="Search"
            aria-label="Search"
        />
    </label>

    @if ($query !== '')
        <div class="mt-2">
            @if ($this->results && $this->results->count())
                <div class="card bg-base-100 border border-base-200 shadow-sm">
                    <div class="card-body p-3 space-y-2">
                        @foreach ($this->results as $post)
                            <a href="{{ route('posts.show', $post) }}" wire:navigate class="block rounded-lg p-2 hover:bg-base-200/60 transition" wire:key="global-search-post-{{ $post->id }}">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="font-semibold truncate">{{ $post->user->name ?? 'Unknown' }}</div>
                                    <div class="opacity-60 shrink-0">&#64;{{ $post->user->username ?? '' }}</div>
                                </div>
                                <div class="text-sm opacity-80 mt-1">
                                    {{ \Illuminate\Support\Str::limit($post->body, 120) }}
                                </div>
                            </a>
                        @endforeach

                        <a href="{{ route('search', ['q' => $query, 'type' => 'posts']) }}" wire:navigate class="text-sm link link-primary">
                            See all results
                        </a>
                    </div>
                </div>
            @else
                <div class="text-sm text-base-content/60">No results yet.</div>
            @endif
        </div>
    @endif
</div>
