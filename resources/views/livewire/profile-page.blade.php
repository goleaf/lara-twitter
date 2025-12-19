<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @include('livewire.partials.profile-header', ['user' => $user, 'active' => 'posts'])

    @if ($user->pinnedPost)
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="text-sm opacity-70">Pinned</div>
                <livewire:post-card :post="$user->pinnedPost" :key="'pinned-'.$user->pinnedPost->id" />
            </div>
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($this->posts as $post)
            <livewire:post-card :post="$post" :key="$post->id" />
        @empty
            @if (! $user->pinnedPost)
                <x-empty-state>
                    No posts yet.
                </x-empty-state>
            @endif
        @endforelse
    </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
