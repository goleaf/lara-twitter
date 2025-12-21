<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
}; ?>

<x-slot:header>
    <div class="text-xl font-semibold">Likes</div>
</x-slot:header>

    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="prose max-w-none space-y-4">
                    <div class="space-y-2">
                        <div class="text-2xl font-bold">Like</div>
                        <p>
                            Likes are a quick way to show appreciation for a post. Tap the heart icon to like or unlike.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">What happens when you like a post</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-semibold">The heart turns red</span> and the like count increases.</li>
                            <li><span class="font-semibold">The author can be notified</span> (depending on their notification settings).</li>
                            <li><span class="font-semibold">It’s saved to your profile</span> under the <span class="font-semibold">Likes</span> tab.</li>
                            <li><span class="font-semibold">It can help surface posts</span> in discovery and trending areas where engagement matters.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Unlike anytime</div>
                        <p>
                            Tap the heart again to unlike. The count will update and the post will be removed from your Likes tab.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Likes vs bookmarks</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-semibold">Likes are public</span> on your profile’s Likes tab.</li>
                            <li><span class="font-semibold">Bookmarks are private</span> and are only visible to you.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Managing notifications</div>
                        <p>
                            You can control whether you receive like notifications in <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a>.
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
