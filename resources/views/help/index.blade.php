@extends('layouts.app')

@section('header')
    <div class="text-xl font-semibold">Help</div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Topics</div>

                <div class="space-y-2 pt-2">
                    <a class="flex items-center justify-between hover:bg-base-200 rounded-box px-2 py-2" href="{{ route('help.hashtags') }}" wire:navigate>
                        <div class="font-medium">Hashtags</div>
                        <div class="text-sm opacity-60">How #tags work</div>
                    </a>
                    <a class="flex items-center justify-between hover:bg-base-200 rounded-box px-2 py-2" href="{{ route('help.profile') }}" wire:navigate>
                        <div class="font-medium">Profile</div>
                        <div class="text-sm opacity-60">Your public page</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
