<x-slot:header>
    <div class="text-xl font-semibold">Blocking</div>
</x-slot:header>

    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="prose max-w-none space-y-4">
                    <div class="space-y-2">
                        <div class="text-2xl font-bold">Block</div>
                        <p>
                            Blocking is the strongest moderation tool on MiniTwitter. It’s designed to help you completely cut off contact
                            with an account that’s harassing, spamming, or bothering you.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">What happens when you block someone</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-semibold">They can’t follow you</span> — and if they already do, they’re removed from your followers.</li>
                            <li><span class="font-semibold">You can’t see each other’s content</span> — profiles and posts are hidden while you’re logged in.</li>
                            <li><span class="font-semibold">Direct Messages are disabled</span> — DMs aren’t available between blocked accounts.</li>
                            <li><span class="font-semibold">No timeline visibility</span> — posts won’t appear in each other’s timelines.</li>
                            <li><span class="font-semibold">No interactions</span> — likes, reposts, bookmarks, quotes, replies, and mentions are blocked.</li>
                            <li><span class="font-semibold">No notifications</span> — blocked accounts won’t receive notifications about you.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Block is one-way</div>
                        <p>
                            Blocking is unidirectional: you can block someone without them blocking you. The blocked account isn’t explicitly notified,
                            but they may discover it if they try to view your profile while logged in.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">How to block and unblock</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Go to a user’s profile (<span class="font-mono">/@username</span>) and use <span class="font-semibold">Block</span>.</li>
                            <li>To unblock later, open <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a> and review <span class="font-semibold">Blocked accounts</span>.</li>
                        </ul>
                        <p>
                            Unblocking restores normal behavior, but it doesn’t automatically restore a follow relationship.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">When to use block</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Stop harassment or abusive behavior.</li>
                            <li>Prevent spam accounts from following or interacting with you.</li>
                            <li>Maintain boundaries and reduce unwanted content.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Block and report</div>
                        <p>
                            If an account is violating the rules, combine blocking with reporting. Reporting helps moderators identify and remove accounts
                            that break the terms.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Limitations</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Blocked users can still view public content while logged out.</li>
                            <li>Someone can create a new account to evade a block.</li>
                            <li>Blocking can’t prevent screenshots or off-platform discussion.</li>
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
