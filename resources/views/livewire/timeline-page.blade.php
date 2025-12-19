<div class="max-w-2xl mx-auto space-y-4">
    <div class="tabs tabs-boxed">
        <a
            class="tab {{ $feed === 'following' ? 'tab-active' : '' }}"
            href="{{ route('timeline', ['feed' => 'following']) }}"
            wire:navigate
        >
            Following
        </a>
        <a
            class="tab {{ $feed === 'for-you' ? 'tab-active' : '' }}"
            href="{{ route('timeline', ['feed' => 'for-you']) }}"
            wire:navigate
        >
            For You
        </a>
    </div>

    @auth
        <livewire:post-composer />
    @endauth

    <div class="space-y-3">
        @foreach ($this->posts as $post)
            <livewire:post-card :post="$post" :key="$post->id" />
        @endforeach
    </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
