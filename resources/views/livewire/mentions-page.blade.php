<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xl font-semibold">Mentions</div>
                    <div class="text-sm opacity-70">Posts that mention you.</div>
                </div>
                <span class="badge badge-outline badge-sm">{{ $this->posts->total() }}</span>
            </div>
        </div>
    </div>

        <div class="space-y-3">
            @forelse ($this->posts as $post)
                <livewire:post-card :post="$post" :key="$post->id" />
            @empty
                <div class="card bg-base-100 border">
                    <div class="card-body">
                        <div class="opacity-70">No mentions yet.</div>
                    </div>
                </div>
            @endforelse
        </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
