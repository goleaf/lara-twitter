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
                    <div class="flex items-center gap-3">
                        <div class="avatar shrink-0">
                            <div class="w-10 rounded-full border border-base-200 bg-base-100">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                @else
                                    <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold truncate">{{ auth()->user()->name }}</div>
                            <div class="text-sm opacity-70 truncate">&#64;{{ auth()->user()->username }}</div>
                        </div>
                    </div>

                    <div class="divider my-3"></div>

                    <div class="font-semibold">Jump to</div>

                    <ul class="menu menu-sm mt-3 rounded-box border border-base-200 bg-base-200/40 p-2">
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
