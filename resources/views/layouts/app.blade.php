<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap">

        @php
            use App\Support\PageTitle;

            $resolvedTitle = PageTitle::resolve($pageTitle ?? $title ?? null);
            $documentTitle = PageTitle::documentTitle($resolvedTitle);
        @endphp

        <title>{{ $documentTitle }}</title>

        <script>
            window.AppConfig = @json([
                'userId' => auth()->check() ? auth()->id() : null,
            ]);
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @filamentStyles
        @livewireStyles
    </head>
    <body class="antialiased bg-base-100 text-base-content bg-ambient">
        <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-0.5">
            <div id="navigate-progress-bar" class="h-full w-0 bg-primary opacity-0 transition-[width,opacity] duration-300"></div>
        </div>

        @php($topbarTitle = $resolvedTitle)
        @php($isTest = app()->runningUnitTests())

        <x-layouts.top-nav :title="$topbarTitle" />

        <div class="min-h-screen max-w-[1280px] mx-auto lg:flex">
            <div class="flex-1 min-w-0 xl:border-r xl:border-base-300">
                <main class="min-h-screen px-4 pt-4 pb-20 lg:pb-4 space-y-4 page-reveal">
                    @if (isset($header) || $__env->hasSection('header'))
                        <div class="card bg-base-100/90 supports-[backdrop-filter]:bg-base-100/70 backdrop-blur">
                            <div class="card-body py-4">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    @yield('header')
                                @endisset
                            </div>
                        </div>
                    @endif

                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>

            @unless ($isTest)
                <div class="hidden xl:block w-[350px] flex-shrink-0">
                    <div class="sticky top-28 pt-2 px-4 space-y-4">
                        <livewire:search.global-search />

                        <livewire:widgets.trending-topics-widget />

                        <livewire:widgets.who-to-follow-widget />

                        <div class="text-xs text-base-content/60 space-y-2 px-4">
                            <div class="flex flex-wrap gap-x-3 gap-y-1">
                                <a href="{{ route('help.index') }}" class="hover:underline" wire:navigate>Help</a>
                                <a href="{{ route('terms') }}" class="hover:underline" wire:navigate>Terms</a>
                                <a href="{{ route('privacy') }}" class="hover:underline" wire:navigate>Privacy</a>
                                <a href="{{ route('cookies') }}" class="hover:underline" wire:navigate>Cookies</a>
                                <a href="{{ route('about') }}" class="hover:underline" wire:navigate>About</a>
                            </div>
                            <div>Copyright {{ date('Y') }} {{ config('app.name', 'MiniTwitter') }}</div>
                        </div>
                    </div>
                </div>
            @endunless
        </div>

        @unless ($isTest)
            <div class="lg:hidden">
                <x-layouts.mobile-nav />
            </div>
        @endunless

        <div id="modal-container"></div>

        @filamentScripts
        @livewireScripts
    </body>
</html>
