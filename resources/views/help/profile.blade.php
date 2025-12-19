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
                        It’s often the first impression people get of you, so a clear and complete profile improves discoverability and trust.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">What you can customize</div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li><span class="font-semibold">Avatar</span> — a square profile picture shown next to your posts.</li>
                        <li><span class="font-semibold">Header image</span> — a banner shown at the top of your profile.</li>
                        <li><span class="font-semibold">Display name</span> — your visible name (can be changed anytime).</li>
                        <li><span class="font-semibold">Username</span> — your unique handle used in mentions and your profile URL.</li>
                        <li><span class="font-semibold">Bio</span> — up to 160 characters describing who you are.</li>
                        <li><span class="font-semibold">Location</span> — optional field showing where you are.</li>
                        <li><span class="font-semibold">Website</span> — one clickable link.</li>
                        <li><span class="font-semibold">Birth date</span> — optional; you can control visibility.</li>
                        <li><span class="font-semibold">Pinned post</span> — one post highlighted at the top of your profile.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">What your profile shows</div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li>Your follower and following counts.</li>
                        <li>Lists you’re included in.</li>
                        <li>Your join date.</li>
                        <li>Tabs for <span class="font-semibold">Posts</span>, <span class="font-semibold">Replies</span>, <span class="font-semibold">Media</span>, and <span class="font-semibold">Likes</span>.</li>
                        <li>A <span class="font-semibold">Verified</span> badge for verified accounts.</li>
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
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <a class="link link-primary" href="{{ route('help.index') }}" wire:navigate>← Back to Help</a>
            </div>
        </div>
    </div>
@endsection

