@extends('layouts.app')

@section('header')
    <div>
        <h2 class="text-xl font-semibold leading-tight">Settings</h2>
        <p class="mt-1 text-sm opacity-70">Manage your account, security, and preferences.</p>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-4">
            <div class="card bg-base-100 border lg:sticky lg:top-24">
                <div class="card-body">
                    <div class="font-semibold">Jump to</div>

                    <ul class="menu menu-sm -mx-2 mt-2">
                        <li class="menu-title"><span>Account</span></li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#profile-information">Profile information</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#password">Password</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#pinned-post">Pinned post</a>
                        </li>

                        <li class="menu-title"><span>Preferences</span></li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#notifications">Notifications</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#muted-terms">Muted terms</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#muted-users">Muted users</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#blocked-users">Blocked users</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#interests">Interests</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#analytics">Analytics</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#timeline">Timeline</a>
                        </li>
                        <li>
                            <a class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#direct-messages">Direct messages</a>
                        </li>

                        <li class="menu-title"><span>Danger zone</span></li>
                        <li>
                            <a class="text-error focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="#delete-account">Delete account</a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <div class="lg:col-span-8 space-y-6">
            <div id="profile-information" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div id="password" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div id="delete-account" class="card bg-base-100 border border-error/30 scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.delete-user-form />
                </div>
            </div>

            <div id="notifications" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.notification-preferences-form />
                </div>
            </div>

            <div id="muted-terms" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.muted-terms-form />
                </div>
            </div>

            <div id="muted-users" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.muted-users-form />
                </div>
            </div>

            <div id="blocked-users" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.blocked-users-form />
                </div>
            </div>

            <div id="interests" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.interest-hashtags-form />
                </div>
            </div>

            <div id="analytics" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.analytics-settings-form />
                </div>
            </div>

            <div id="pinned-post" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.pinned-post-form />
                </div>
            </div>

            <div id="timeline" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.timeline-settings-form />
                </div>
            </div>

            <div id="direct-messages" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.direct-message-settings-form />
                </div>
            </div>
        </div>
    </div>
@endsection
