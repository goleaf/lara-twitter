@props(['title' => null])

@php
    use App\Support\PageTitle;

    $title = PageTitle::resolve($title);
@endphp

<div class="flex items-center justify-between px-4 h-16 topbar-shell">
    <div class="min-w-0 leading-tight">
        <div class="topbar-kicker">{{ config('app.name', 'MiniTwitter') }}</div>
        <h1 class="text-xl font-semibold truncate">{{ $title }}</h1>
    </div>

    @auth
        <div class="flex items-center gap-2">
            <a href="{{ route('timeline') }}#composer" wire:navigate class="btn btn-primary btn-sm gap-2">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 0 1 3.182 3.182L7.5 19.313l-4.5 1.125 1.125-4.5L16.862 3.487Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6.75 17.25 4.5" />
                </svg>
                <span>Post</span>
            </a>
        </div>
    @endauth
</div>
