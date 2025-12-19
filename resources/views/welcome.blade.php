@extends('layouts.app')

@section('header')
    <div class="text-xl font-semibold">Welcome</div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body space-y-3">
                <div class="text-2xl font-bold">Lara Twitter</div>
                <div class="text-sm opacity-70">
                    A tiny Twitter-style app built with Laravel 12, Livewire v3, Tailwind, and daisyUI.
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <a class="btn btn-primary btn-sm" href="{{ route('timeline') }}" wire:navigate>Open timeline</a>

                    @auth
                        <a class="btn btn-ghost btn-sm" href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate>
                            View profile
                        </a>
                        <a class="btn btn-ghost btn-sm" href="{{ route('messages.index') }}" wire:navigate>
                            Messages
                        </a>
                    @else
                        <a class="btn btn-ghost btn-sm" href="{{ route('login') }}" wire:navigate>Log in</a>
                        @if (Route::has('register'))
                            <a class="btn btn-outline btn-sm" href="{{ route('register') }}" wire:navigate>Register</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">What you can do</div>
                    <ul class="list-disc list-inside space-y-1 text-sm opacity-70 pt-2">
                        <li>Post, reply, repost, and quote.</li>
                        <li>Search by hashtags, mentions, and operators.</li>
                        <li>Explore trends and curated Moments.</li>
                        <li>Chat in direct messages (including groups).</li>
                    </ul>
                </div>
            </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Quick links</div>
                    <div class="space-y-1 pt-2">
                        <a class="btn btn-ghost btn-sm justify-start w-full" href="{{ route('trending') }}" wire:navigate>Trending</a>
                        <a class="btn btn-ghost btn-sm justify-start w-full" href="{{ route('explore') }}" wire:navigate>Explore</a>
                        <a class="btn btn-ghost btn-sm justify-start w-full" href="{{ route('help.index') }}" wire:navigate>Help</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

