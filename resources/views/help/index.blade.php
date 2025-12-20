@extends('layouts.app')

@section('header')
    <div class="text-xl font-semibold">Help</div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Topics</div>

                <div class="space-y-1 pt-2">
                        <x-list-row href="{{ route('help.blocking') }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-medium">Block</div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">Cut off an account</div>
                        </x-list-row>
                        <x-list-row href="{{ route('help.mute') }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-medium">Mute</div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">Hide without blocking</div>
                        </x-list-row>
                        <x-list-row href="{{ route('help.direct-messages') }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-medium">Direct Messages</div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">Private chats</div>
                        </x-list-row>
                        <x-list-row href="{{ route('help.replies') }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-medium">Replies</div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">Threads and conversations</div>
                        </x-list-row>
                        <x-list-row href="{{ route('help.likes') }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-medium">Likes</div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">Heart reactions</div>
                        </x-list-row>
                        <x-list-row href="{{ route('help.mentions') }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-medium">Mentions</div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">Tag and notify accounts</div>
                        </x-list-row>
                        <x-list-row href="{{ route('help.hashtags') }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-medium">Hashtags</div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">How #tags work</div>
                        </x-list-row>
                        <x-list-row href="{{ route('help.profile') }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-medium">Profile</div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">Your public page</div>
                        </x-list-row>
                    </div>
                </div>
            </div>
        </div>
@endsection
