<x-slot:header>
    <div class="text-xl font-semibold">Mute</div>
</x-slot:header>

    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="prose max-w-none space-y-4">
                    <div class="space-y-2">
                        <div class="text-2xl font-bold">Mute</div>
                        <p>
                            Muting is a softer moderation option than <a class="link link-primary" href="{{ route('help.blocking') }}" wire:navigate>blocking</a>.
                            It lets you hide content from specific accounts without stopping them from interacting with you — and without them being notified.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">What happens when you mute an account</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-semibold">No timeline posts</span> — you won’t see their posts in your timeline.</li>
                            <li><span class="font-semibold">No post notifications</span> — likes, reposts, replies, and mentions from that account won’t show in your notifications.</li>
                            <li><span class="font-semibold">Retweets are hidden</span> — their reposts/quotes won’t appear in your feed.</li>
                            <li><span class="font-semibold">Still interactive</span> — they can still follow you, see your posts, reply to you, and send DMs (unless your DM settings restrict them).</li>
                            <li><span class="font-semibold">Invisible + one-way</span> — the muted account isn’t notified, and muting doesn’t change anything on their side.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Muted words (keywords, phrases, hashtags)</div>
                        <p>
                            You can also mute specific words, phrases, or hashtags to hide posts that contain them.
                            This is useful for avoiding spoilers, reducing noise from viral trends, or filtering unwanted topics.
                        </p>
                        <p>
                            Manage muted words in <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a> under <span class="font-semibold">Muted words</span>.
                        </p>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-semibold">Duration</span> — mute forever or for a limited time.</li>
                            <li><span class="font-semibold">Where it applies</span> — apply to your timeline, notifications, or both.</li>
                            <li><span class="font-semibold">Whole word</span> — best-effort whole-word matching for simple terms.</li>
                            <li><span class="font-semibold">Non-followed only</span> — optionally apply only to people you don’t follow.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Manage and undo</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Mute/unmute from a user’s profile (<span class="font-mono">/@username</span>) using <span class="font-semibold">Mute</span>.</li>
                            <li>Review muted accounts in <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a> under <span class="font-semibold">Muted accounts</span>.</li>
                            <li>Review muted words in <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a> under <span class="font-semibold">Muted words</span>.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Limitations</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>You might miss important context if a muted account mentions you or replies.</li>
                            <li>Muted-word matching is best-effort and may not catch every variation.</li>
                        </ul>
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
