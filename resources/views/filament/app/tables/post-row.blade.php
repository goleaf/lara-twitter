@php
    /** @var \App\Models\Post $record */
    $record = $getRecord();
    $user = $record->user;
@endphp

<div class="flex gap-3 py-4">
    <div class="shrink-0">
        @if ($user->avatar_url)
            <img
                src="{{ $user->avatar_url }}"
                alt="{{ $user->name }}"
                class="h-10 w-10 rounded-full object-cover"
                loading="lazy"
            />
        @else
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-700">
                {{ mb_substr($user->name ?? $user->username ?? '?', 0, 1) }}
            </div>
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('profile.show', ['user' => $user->username]) }}" class="font-semibold hover:underline">
                {{ $user->name }}
            </a>

            <span class="text-gray-500">
                @{{ $user->username }}
            </span>

            <span class="text-gray-400">·</span>

            <a href="{{ route('posts.show', $record) }}" class="text-gray-500 hover:underline">
                {{ $record->created_at?->diffForHumans() }}
            </a>
        </div>

        <div class="mt-1 text-sm leading-relaxed">
            {!! app(\App\Services\PostBodyRenderer::class)->render($record->body, $record->id) !!}
        </div>

        @if ($record->images->isNotEmpty())
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach ($record->images as $image)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                        alt="Post image"
                        class="h-40 w-full rounded-xl object-cover"
                        loading="lazy"
                    />
                @endforeach
            </div>
        @endif

        <div class="mt-3 flex items-center gap-6 text-xs text-gray-500">
            <span class="inline-flex items-center gap-1">
                <x-filament::icon icon="heroicon-o-chat-bubble-left" class="h-4 w-4" />
                {{ number_format($record->replies_count) }}
            </span>
            <span class="inline-flex items-center gap-1">
                <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                {{ number_format($record->reposts_count) }}
            </span>
            <span class="inline-flex items-center gap-1">
                <x-filament::icon icon="heroicon-o-heart" class="h-4 w-4" />
                {{ number_format($record->likes_count) }}
            </span>
        </div>
    </div>
</div>
