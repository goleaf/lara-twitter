@php
    $user = auth()->user();
    $jumpSections = [
        'Account' => [
            ['id' => 'profile-information', 'label' => 'Profile information'],
            ['id' => 'password', 'label' => 'Password'],
            ['id' => 'pinned-post', 'label' => 'Pinned post'],
        ],
        'Preferences' => [
            ['id' => 'notifications', 'label' => 'Notifications'],
            ['id' => 'timeline', 'label' => 'Timeline'],
            ['id' => 'direct-messages', 'label' => 'Direct messages'],
            ['id' => 'interests', 'label' => 'Interests'],
            ['id' => 'analytics', 'label' => 'Analytics'],
        ],
        'Safety' => [
            ['id' => 'muted-terms', 'label' => 'Muted terms'],
            ['id' => 'muted-users', 'label' => 'Muted users'],
            ['id' => 'blocked-users', 'label' => 'Blocked users'],
        ],
        'Danger zone' => [
            ['id' => 'delete-account', 'label' => 'Delete account', 'danger' => true],
        ],
    ];
@endphp

<x-slot:header>
    <div>
        <h2 class="text-xl font-semibold leading-tight">Settings</h2>
        <p class="mt-1 text-sm opacity-70">Manage your account, security, and preferences.</p>
    </div>
</x-slot:header>

    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-4">
            <div class="space-y-6 lg:sticky lg:top-24">
                <div class="card bg-base-100 border">
                    <div class="card-body gap-4">
                        <div class="flex items-start gap-3">
                            <div class="avatar shrink-0">
                                <div class="w-12 rounded-full border border-base-200 bg-base-100">
                                    @if ($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold truncate">{{ $user->name }}</div>
                                <div class="text-sm opacity-70 truncate">&#64;{{ $user->username }}</div>
                                @if ($user->is_verified || $user->is_premium || $user->is_admin)
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if ($user->is_verified)
                                            <span class="badge badge-outline badge-sm">Verified</span>
                                        @endif
                                        @if ($user->is_premium)
                                            <span class="badge badge-secondary badge-sm">Premium</span>
                                        @endif
                                        @if ($user->is_admin)
                                            <span class="badge badge-accent badge-sm">Admin</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-box border border-base-200/70 bg-base-200/40 p-3">
                                <div class="text-xs uppercase tracking-[0.3em] opacity-60">Email</div>
                                <div class="truncate text-sm font-medium">{{ $user->email }}</div>
                                <div class="text-xs {{ $user->email_verified_at ? 'text-success' : 'text-warning' }}">
                                    {{ $user->email_verified_at ? 'Verified' : 'Not verified' }}
                                </div>
                            </div>
                            <div class="rounded-box border border-base-200/70 bg-base-200/40 p-3">
                                <div class="text-xs uppercase tracking-[0.3em] opacity-60">Direct messages</div>
                                <div class="text-sm font-medium capitalize">{{ $user->dm_policy ?: 'everyone' }}</div>
                                <div class="text-xs opacity-60">
                                    {{ $user->dm_allow_requests ? 'Requests enabled' : 'Requests off' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a class="btn btn-sm btn-outline" href="{{ route('profile.show', ['user' => $user->username]) }}" wire:navigate>
                                View public profile
                            </a>
                            <a class="btn btn-sm btn-ghost" href="{{ route('help.profile') }}" wire:navigate>
                                Profile help
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 border">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold">Jump to</div>
                            <div class="text-xs uppercase tracking-[0.3em] opacity-60">Settings</div>
                        </div>

                        <div class="mt-4 space-y-4">
                            <nav class="lg:hidden space-y-4" aria-label="Settings sections">
                                @foreach ($jumpSections as $section => $links)
                                    <div class="space-y-2" wire:key="jump-section-mobile-{{ md5($section) }}">
                                        <div class="text-xs uppercase tracking-[0.3em] opacity-60">{{ $section }}</div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($links as $link)
                                                <a
                                                    class="btn btn-xs btn-outline rounded-full focus-ring {{ ($link['danger'] ?? false) ? 'text-error border-error/40 hover:border-error/70 hover:text-error' : '' }}"
                                                    href="#{{ $link['id'] }}"
                                                    wire:key="jump-link-mobile-{{ $link['id'] }}"
                                                >
                                                    {{ $link['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </nav>

                            <nav class="hidden lg:block" aria-label="Settings sections">
                                <ul class="menu menu-sm rounded-box border border-base-200 bg-base-200/40 p-2">
                                    @foreach ($jumpSections as $section => $links)
                                        <li class="menu-title" wire:key="jump-section-desktop-{{ md5($section) }}"><span>{{ $section }}</span></li>
                                        @foreach ($links as $link)
                                            <li wire:key="jump-link-desktop-{{ $link['id'] }}">
                                                <a
                                                    class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 {{ ($link['danger'] ?? false) ? 'text-error' : '' }}"
                                                    href="#{{ $link['id'] }}"
                                                >
                                                    {{ $link['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="lg:col-span-8 space-y-6">
            <div class="flex items-center gap-3 text-[0.65rem] uppercase tracking-[0.35em] text-base-content/50">
                <span>Account</span>
                <span class="h-px flex-1 bg-base-200/80"></span>
            </div>

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

            <div id="pinned-post" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.pinned-post-form />
                </div>
            </div>

            <div class="flex items-center gap-3 text-[0.65rem] uppercase tracking-[0.35em] text-base-content/50">
                <span>Preferences</span>
                <span class="h-px flex-1 bg-base-200/80"></span>
            </div>

            <div id="notifications" class="card bg-base-100 border scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.notification-preferences-form />
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

            <div class="flex items-center gap-3 text-[0.65rem] uppercase tracking-[0.35em] text-base-content/50">
                <span>Safety</span>
                <span class="h-px flex-1 bg-base-200/80"></span>
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

            <div class="flex items-center gap-3 text-[0.65rem] uppercase tracking-[0.35em] text-base-content/50">
                <span>Danger zone</span>
                <span class="h-px flex-1 bg-base-200/80"></span>
            </div>

            <div id="delete-account" class="card bg-base-100 border border-error/30 scroll-mt-24">
                <div class="card-body">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
