<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="theme-color" content="#FDF6EE">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;700&family=Sora:wght@400;500;600;700&display=swap">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;700&family=Sora:wght@400;500;600;700&display=swap">

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
        @livewireStyles
    </head>
    <body class="antialiased min-h-screen bg-base-100 text-base-content bg-ambient">
        <a href="#main-content" class="skip-link">Skip to content</a>
        <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-0.5" aria-hidden="true">
            <div id="navigate-progress-bar" class="h-full w-0 bg-primary opacity-0 transition-[width,opacity] duration-300"></div>
        </div>

        @php($topbarTitle = $resolvedTitle)
        @php($isTest = app()->runningUnitTests())

        <x-layouts.top-nav :title="$topbarTitle" />

        <div class="app-shell min-h-screen lg:flex">
            <div class="flex-1 min-w-0 xl:border-r xl:border-base-300">
                <main id="main-content" tabindex="-1" class="min-h-screen px-4 pt-8 pb-24 lg:px-6 lg:pt-10 lg:pb-10 space-y-6 page-reveal focus:outline-none">
                    @if (isset($header))
                        <div class="glass-panel">
                            <div class="card-body py-4">
                                {{ $header }}
                            </div>
                        </div>
                    @endif

                    {{ $slot ?? '' }}
                </main>
            </div>

            @unless ($isTest)
                <div class="hidden xl:block w-[350px] flex-shrink-0">
                    <div class="sticky top-28 pt-2 px-4 space-y-4">
                        <livewire:search.global-search lazy />

                        <livewire:widgets.trending-topics-widget lazy />

                        <livewire:widgets.who-to-follow-widget lazy />
                    </div>
                </div>
            @endunless
        </div>

        <footer class="mt-8 border-t border-base-200/70 bg-base-100/90 backdrop-blur">
            <div class="max-w-[1360px] mx-auto px-4 lg:px-6 pt-4 pb-20 lg:pb-8">
                <div class="flex flex-col gap-2 text-xs text-base-content/60 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <a href="{{ route('help.index') }}" class="hover:underline" wire:navigate>Help</a>
                        <a href="{{ route('terms') }}" class="hover:underline" wire:navigate>Terms</a>
                        <a href="{{ route('privacy') }}" class="hover:underline" wire:navigate>Privacy</a>
                        <a href="{{ route('cookies') }}" class="hover:underline" wire:navigate>Cookies</a>
                        <a href="{{ route('about') }}" class="hover:underline" wire:navigate>About</a>
                    </div>
                    <div>Copyright 2025 Laravel</div>
                </div>
            </div>
        </footer>

        @unless ($isTest)
            <x-layouts.mobile-sidebar />
            <div class="lg:hidden">
                <x-layouts.mobile-nav />
            </div>
        @endunless

        <div id="modal-container"></div>

        @livewireScripts
    </body>
</html>
