<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-base-200 bg-base-100/90 backdrop-blur supports-[backdrop-filter]:bg-base-100/70 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="navbar max-w-7xl w-full mx-auto px-4">
        <div class="flex-1 gap-2">
            <a class="btn btn-ghost btn-square" href="{{ route('dashboard') }}" wire:navigate aria-label="{{ config('app.name', 'MiniTwitter') }}">
                <x-application-logo class="h-7 w-auto fill-current" />
            </a>

            <!-- Navigation Links -->
            <div class="hidden sm:flex items-center gap-1">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </x-nav-link>
            </div>
        </div>

        <!-- Settings Dropdown -->
        <div class="flex-none gap-1">
            <div class="hidden sm:block">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="btn btn-ghost btn-sm gap-2">
                            <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>

                            <svg class="h-4 w-4 opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Settings') }}
                        </x-dropdown-link>

                        <x-dropdown-link wire:click="logout">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <button
                @click="open = ! open"
                class="btn btn-ghost btn-square btn-sm sm:hidden"
                :aria-expanded="open.toString()"
                aria-controls="responsive-menu"
                aria-label="Toggle menu"
            >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div id="responsive-menu" :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-base-200 bg-base-100/80 supports-[backdrop-filter]:bg-base-100/60 backdrop-blur">
        <div class="p-3 flex flex-col gap-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="p-3 border-t border-base-200">
            <div class="font-medium" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
            <div class="text-sm opacity-70">{{ auth()->user()->email }}</div>

            <div class="mt-2 flex flex-col gap-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Settings') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link wire:click="logout">
                    {{ __('Log Out') }}
                </x-responsive-nav-link>
            </div>
        </div>
    </div>
</nav>
