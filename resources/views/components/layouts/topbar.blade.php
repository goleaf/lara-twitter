@props(['title' => null])

@php
    use App\Support\PageTitle;

    $title = PageTitle::resolve($title);
@endphp

<div class="flex items-center justify-between px-4 h-14">
    <h1 class="text-xl font-bold truncate">{{ $title }}</h1>

    @auth
        <div class="flex items-center gap-2">
            <a href="{{ route('timeline') }}#composer" wire:navigate class="btn btn-primary btn-sm">Post</a>
        </div>
    @endauth
</div>
