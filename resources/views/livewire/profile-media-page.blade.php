<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @include('livewire.partials.profile-header', ['user' => $user, 'active' => 'media'])

    <div class="space-y-3">
        @forelse ($this->posts as $post)
            <livewire:post-card :post="$post" :key="$post->id" />
        @empty
            <x-empty-state>
                No media posts yet.
            </x-empty-state>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
