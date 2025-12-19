<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @include('livewire.partials.profile-header', ['user' => $user, 'active' => 'likes'])

    <div class="space-y-3">
        @foreach ($this->posts as $post)
            <livewire:post-card :post="$post" :key="$post->id" />
        @endforeach
    </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
