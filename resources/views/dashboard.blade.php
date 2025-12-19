@extends('layouts.app')

@section('header')
    <div class="space-y-1">
        <h2 class="text-xl font-semibold leading-tight">Dashboard</h2>
        <p class="text-sm opacity-70">Quick actions and shortcuts.</p>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto space-y-4">
        <div class="card bg-base-100 border overflow-hidden">
            <div class="card-body">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-2xl font-bold tracking-tight">You’re logged in</div>
                        <div class="text-sm opacity-70 pt-1">Jump back into your timeline or open your profile.</div>
                    </div>
                    <div class="shrink-0">
                        <a class="btn btn-primary btn-sm" href="{{ route('timeline') }}" wire:navigate>Home</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4">
                    <a class="flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-4 py-3 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate>
                        <div class="min-w-0">
                            <div class="font-semibold">Profile</div>
                            <div class="text-sm opacity-70 truncate">&#64;{{ auth()->user()->username }}</div>
                        </div>
                        <div class="text-sm opacity-60">View</div>
                    </a>

                    <a class="flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-4 py-3 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('messages.index') }}" wire:navigate>
                        <div class="min-w-0">
                            <div class="font-semibold">Messages</div>
                            <div class="text-sm opacity-70">Direct messages and groups</div>
                        </div>
                        <div class="text-sm opacity-60">Open</div>
                    </a>

                    <a class="flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-4 py-3 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('bookmarks') }}" wire:navigate>
                        <div class="min-w-0">
                            <div class="font-semibold">Bookmarks</div>
                            <div class="text-sm opacity-70">Saved posts</div>
                        </div>
                        <div class="text-sm opacity-60">Open</div>
                    </a>

                    <a class="flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-4 py-3 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('profile') }}" wire:navigate>
                        <div class="min-w-0">
                            <div class="font-semibold">Settings</div>
                            <div class="text-sm opacity-70">Account and preferences</div>
                        </div>
                        <div class="text-sm opacity-60">Manage</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
