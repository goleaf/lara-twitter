<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="text-2xl font-bold">#{{ $tag }}</div>

            <div class="tabs tabs-boxed mt-4">
                <button type="button" class="tab {{ $sort === 'latest' ? 'tab-active' : '' }}" wire:click="$set('sort', 'latest')">
                    Latest
                </button>
                <button type="button" class="tab {{ $sort === 'top' ? 'tab-active' : '' }}" wire:click="$set('sort', 'top')">
                    Top
                </button>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        @foreach ($this->posts as $post)
            <livewire:post-card :post="$post" :key="$post->id" />
        @endforeach
    </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
