<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border overflow-hidden">
        @if ($user->header_url)
            <div class="h-32 sm:h-40 bg-cover bg-center" style="background-image: url('{{ $user->header_url }}')"></div>
        @else
            <div class="h-32 sm:h-40 bg-base-200"></div>
        @endif

        <div class="card-body">
            <div class="-mt-12 flex items-end justify-between gap-4">
                <div class="flex items-end gap-3">
                    <div class="avatar">
                        <div class="w-20 rounded-full border border-base-200 bg-base-100">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="" />
                            @else
                                <div class="bg-base-200 grid place-items-center h-full w-full text-xl font-semibold">
                                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-xl font-bold">
                            {{ $user->name }}
                            @if ($user->is_verified)
                                <span class="badge badge-primary badge-sm align-middle ms-1">Verified</span>
                            @endif
                        </div>
                        <div class="opacity-60">&#64;{{ $user->username }}</div>
                    </div>
                </div>
            </div>

            <div class="tabs tabs-boxed mt-4">
                <a class="tab" href="{{ route('profile.show', ['user' => $user]) }}" wire:navigate>Posts</a>
                <a class="tab" href="{{ route('profile.likes', ['user' => $user]) }}" wire:navigate>Likes</a>
                <a class="tab" href="{{ route('profile.replies', ['user' => $user]) }}" wire:navigate>Replies</a>
                <a class="tab tab-active" href="{{ route('profile.media', ['user' => $user]) }}" wire:navigate>Media</a>
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

