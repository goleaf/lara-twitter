@extends('layouts.app')

@section('header')
    <div class="text-xl font-semibold">Profile</div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body space-y-4">
                <div class="space-y-2">
                    <div class="text-2xl font-bold">Profile</div>
                    <p class="opacity-80">
                        Your profile is your identity and home base, serving as a public-facing page that represents you or your brand.
                        It’s often the first impression people get of you, so optimization matters for personal branding, networking, and professional presence.
                    </p>
                    <p class="opacity-80">
                        Your profile includes your post history, allowing others to browse what you’ve posted publicly (unless deleted).
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">What you can customize</div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li><span class="font-semibold">Avatar</span> — a square profile picture shown next to all your posts (typically 400×400).</li>
                        <li><span class="font-semibold">Header image</span> — a large banner at the top of your profile (1500×500 recommended).</li>
                        <li><span class="font-semibold">Display name</span> — your visible name (can be changed anytime; can include emojis or any characters).</li>
                        <li><span class="font-semibold">Username</span> — your unique handle; part of your profile URL and how people mention you. Changing it affects how people find and mention you.</li>
                        <li><span class="font-semibold">Bio</span> — up to 160 characters describing yourself (keywords, interests, credentials, personality).</li>
                        <li><span class="font-semibold">Location</span> — optional field showing where you are (or a humorous alternative).</li>
                        <li><span class="font-semibold">Website</span> — one clickable link (often your main website).</li>
                        <li><span class="font-semibold">Birth date</span> — optional; you can control visibility.</li>
                        <li><span class="font-semibold">Pinned post</span> — one post you can highlight at the top of your profile.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">What your profile shows</div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li>Your follower count, following count, and lists you’re included in.</li>
                        <li>Your join date (automatically shown).</li>
                        <li>A <span class="font-semibold">Verified</span> badge for verified accounts.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Tabs</div>
                    <p class="opacity-80">
                        Your profile is organized into tabs so visitors can browse different types of content:
                    </p>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li><span class="font-semibold">Posts</span></li>
                        <li><span class="font-semibold">Replies</span></li>
                        <li><span class="font-semibold">Media</span></li>
                        <li><span class="font-semibold">Likes</span></li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Profile URL</div>
                    <p class="opacity-80">
                        Your public profile lives at <span class="font-mono">/@username</span>, which makes it easy to share.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Tips</div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li>Use keywords in your bio to help people find you.</li>
                        <li>Pin a post that represents what you want new visitors to see first.</li>
                        <li>Keep your avatar and header consistent with your brand.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Where to edit</div>
                    <p class="opacity-80">
                        Update your profile in <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a>.
                    </p>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <a class="btn btn-ghost btn-sm" href="{{ route('help.index') }}" wire:navigate>← Back to Help</a>
            </div>
        </div>
    </div>
@endsection
