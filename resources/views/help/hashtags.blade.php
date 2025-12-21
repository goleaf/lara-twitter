<x-slot:header>
    <div class="text-xl font-semibold">Hashtags</div>
</x-slot:header>

    <div class="max-w-2xl mx-auto space-y-4">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="prose max-w-none space-y-4">
                    <div class="space-y-2">
                        <div class="text-2xl font-bold">Hashtags</div>
                        <p>
                            Hashtags are metadata tags created by placing the <span class="font-mono">#</span> symbol directly before a word (without spaces),
                            turning that text into a clickable link that aggregates all posts using the same hashtag.
                        </p>
                        <p>
                            They were invented organically by Twitter users in 2007 and later adopted by Twitter, and they’ve since been adopted by other platforms.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">What hashtags do</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Categorize content by topic and make posts discoverable beyond your followers.</li>
                            <li>Help you join broader conversations, movements, and events.</li>
                            <li>Enable tracking campaigns and participating in trending topics.</li>
                            <li>Build communities around shared interests.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Top vs Latest</div>
                        <p>
                            When you click a hashtag, you can switch between <span class="font-semibold">Top</span> (most engaged)
                            and <span class="font-semibold">Latest</span> (chronological).
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Rules</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Hashtags can contain letters, numbers, and underscores.</li>
                            <li>They can’t contain spaces or special characters.</li>
                            <li>Using 1–3 relevant hashtags per post is usually best; overuse is considered spam.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Examples</div>
                        <div class="flex flex-wrap gap-2">
                            <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => 'BlackTwitter']) }}" wire:navigate>#BlackTwitter</a>
                            <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => 'AcademicTwitter']) }}" wire:navigate>#AcademicTwitter</a>
                            <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => 'ThrowbackThursday']) }}" wire:navigate>#ThrowbackThursday</a>
                            <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => 'FollowFriday']) }}" wire:navigate>#FollowFriday</a>
                            <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => 'MeToo']) }}" wire:navigate>#MeToo</a>
                            <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => 'BlackLivesMatter']) }}" wire:navigate>#BlackLivesMatter</a>
                            <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => 'ClimateStrike']) }}" wire:navigate>#ClimateStrike</a>
                        </div>
                        <p class="text-sm opacity-70">
                            Hashtags can be hijacked or used ironically, and controversial hashtags sometimes get flooded with opposing views.
                            Companies also create branded hashtags for marketing campaigns.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="font-semibold">Trends</div>
                        <p>
                            Trending hashtags are often customized by location and interests.
                            See <a class="link link-primary" href="{{ route('trending') }}" wire:navigate>Trending</a>, and set your interests in
                            <a class="link link-primary" href="{{ route('profile') }}" wire:navigate>Settings</a>.
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
