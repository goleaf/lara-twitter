@extends('layouts.app')

@section('header')
    <div class="text-xl font-semibold">Mentions</div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body space-y-4">
                <div class="space-y-2">
                    <div class="text-2xl font-bold">Mentions (&#64;username)</div>
                    <p class="opacity-80">
                        Mentions use the <span class="font-mono">&#64;</span> symbol followed immediately by a username to tag someone in a post. When you mention
                        an account, you’re making that post part of the public conversation around them.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">What happens when you mention someone</div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li>The mentioned username becomes a clickable link to their profile.</li>
                        <li>The mentioned user may receive a notification (depending on their notification settings).</li>
                        <li>The post appears in the mentioned user’s <a class="link link-primary" href="{{ route('mentions') }}" wire:navigate>Mentions</a> feed.</li>
                    </ul>
                    <p class="text-sm opacity-70">
                        If you start a post with a mention (like <span class="font-mono">&#64;username hi</span>), it’s treated as a reply-like post with limited visibility;
                        using <span class="font-mono">.@username</span> keeps it as a normal post.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Why mentions are used</div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li>Directly addressing someone in a conversation.</li>
                        <li>Giving credit or attribution.</li>
                        <li>Inviting someone into a thread or group discussion.</li>
                        <li>Asking questions of brands, creators, or public figures.</li>
                        <li>Calling someone out publicly (sometimes constructively, sometimes not).</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Multiple mentions</div>
                    <p class="opacity-80">
                        You can mention multiple accounts in a single post. Each mention is just text and counts toward your character limit.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Harassment and unwanted mentions</div>
                    <p class="opacity-80">
                        Mentions can be abused to spam notifications. If you’re being targeted, use <a class="link link-primary" href="{{ route('help.mute') }}" wire:navigate>mute</a> or
                        <a class="link link-primary" href="{{ route('help.blocking') }}" wire:navigate>block</a>.
                    </p>
                    <p class="opacity-80">
                        You can also control mention notifications in <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a> under <span class="font-semibold">Notifications</span>.
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
