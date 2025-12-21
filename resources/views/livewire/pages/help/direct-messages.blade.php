<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
}; ?>

<x-slot:header>
    <div class="text-xl font-semibold">Direct Messages</div>
</x-slot:header>

    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="prose max-w-none space-y-4">
                    <div class="space-y-2">
                        <div class="text-2xl font-bold">Direct Messages (DMs)</div>
                        <p>
                            Direct Messages are private conversations separate from public posts. You can use DMs for one-on-one chats
                            or group chats (up to 50 people), without posting publicly.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">What DMs support</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-semibold">Text</span> — up to 10,000 characters per message.</li>
                            <li><span class="font-semibold">Attachments</span> — photos, videos, GIFs, and audio files.</li>
                            <li><span class="font-semibold">Reactions</span> — react to messages with emojis.</li>
                            <li><span class="font-semibold">Typing indicator</span> — see when someone is typing.</li>
                            <li><span class="font-semibold">Read receipts</span> — “Seen” status if enabled by both people.</li>
                            <li><span class="font-semibold">Unsend</span> — remove a recently sent message for everyone.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Message requests</div>
                        <p>
                            If someone who doesn’t meet your DM policy tries to start a conversation and you allow requests,
                            it appears under <span class="font-semibold">Requests</span>. Accept to reply and move it to your inbox.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Privacy settings</div>
                        <p>
                            You can control who can message you in <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a>:
                        </p>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-semibold">Everyone</span> — anyone can start a DM.</li>
                            <li><span class="font-semibold">Only people you follow</span> — others become requests (if allowed).</li>
                            <li><span class="font-semibold">No one</span> — blocks new DMs.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Inbox tools</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-semibold">Search</span> — search conversations by people or message content.</li>
                            <li><span class="font-semibold">Pin</span> — keep important conversations at the top.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Groups</div>
                        <p>
                            Group chats can be named and managed by the group admin (add/remove members and update the title).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <a class="btn btn-ghost btn-sm" href="{{ route('help.index') }}" wire:navigate>← Back to Help</a>
            </div>
        </div>
    </div>
