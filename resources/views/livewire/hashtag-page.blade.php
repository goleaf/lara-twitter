<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @php($hashtagModel = $this->hashtag)

    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="text-xl font-semibold">#{{ $tag }}</div>
                        @if (! is_null($hashtagModel?->posts_count))
                            <span class="badge badge-ghost badge-sm">{{ $hashtagModel->posts_count }} posts</span>
                        @endif
                    </div>
                    <div class="text-sm opacity-70">Posts with this hashtag.</div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a class="btn btn-ghost btn-sm" href="{{ route('search', ['q' => '#'.$tag, 'type' => 'posts']) }}" wire:navigate>
                        Search
                    </a>

                    @if ($hashtagModel)
                        <livewire:report-button
                            :reportable-type="\App\Models\Hashtag::class"
                            :reportable-id="$hashtagModel->id"
                            label="Report"
                            button-class="btn btn-ghost btn-sm"
                            :key="'report-tag-'.$hashtagModel->id"
                        />
                    @endif
                </div>
            </div>

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
