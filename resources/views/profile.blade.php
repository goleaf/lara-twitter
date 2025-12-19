@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold leading-tight">Settings</h2>
@endsection

@section('content')
        <div class="max-w-3xl mx-auto space-y-6">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.delete-user-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.notification-preferences-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.muted-terms-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.blocked-users-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.interest-hashtags-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.analytics-settings-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.pinned-post-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.timeline-settings-form />
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <livewire:profile.direct-message-settings-form />
            </div>
        </div>
    </div>
@endsection
