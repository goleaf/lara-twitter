@extends('layouts.app')

@section('header')
    <div class="text-xl font-semibold">Replies</div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body space-y-4">
                <div class="space-y-2">
                    <div class="text-2xl font-bold">Reply</div>
                    <p class="opacity-80">
                        Replies enable direct conversation and create threaded discussions beneath posts. When you reply, your message appears indented
                        below the post you’re responding to, creating a visible conversation thread.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Mentions</div>
                    <p class="opacity-80">
                        Replies automatically prefill an <span class="font-mono">&#64;</span>mention of the original author and any other users mentioned
                        in the post you’re replying to. You can remove these mentions before posting.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Notifications</div>
                    <p class="opacity-80">
                        The original author receives a notification of your reply unless they’ve muted or blocked you, or they’ve limited notifications.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Conversation threads</div>
                    <p class="opacity-80">
                        Replies form a conversation tree: multiple people can reply to the same post, and others can reply to those replies, creating
                        branching discussions. Click any post’s timestamp to open the full thread view.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Reply permissions</div>
                    <p class="opacity-80">
                        The author can control who can reply:
                    </p>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li><span class="font-semibold">Everyone</span> — anyone can reply.</li>
                        <li><span class="font-semibold">Only people you follow</span> — only accounts the author follows can reply.</li>
                        <li><span class="font-semibold">Only people you mention</span> — only accounts mentioned in the post can reply.</li>
                        <li><span class="font-semibold">No one</span> — replies are disabled.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Where replies show up</div>
                    <p class="opacity-80">
                        Replies are posts. They appear on your profile under <span class="font-semibold">Replies</span> and are a core part of the
                        real-time conversational nature that defines Twitter-like platforms.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="font-semibold">Why replies matter</div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        <li>Community building.</li>
                        <li>Customer service.</li>
                        <li>Debate, clarification, and context.</li>
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

