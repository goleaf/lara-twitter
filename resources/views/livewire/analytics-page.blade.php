@php($s = $this->summary)

<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="text-xl font-semibold">Analytics</div>
            <div class="text-sm opacity-70 pt-1">MVP: unique views per day (not total impressions).</div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="text-sm opacity-70">Post views</div>
                <div class="text-2xl font-semibold">{{ $s['post_views_7d'] }}</div>
                <div class="text-xs opacity-60">Last 7 days · {{ $s['post_views_30d'] }} / 30d</div>
            </div>
        </div>
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="text-sm opacity-70">Profile visits</div>
                <div class="text-2xl font-semibold">{{ $s['profile_views_7d'] }}</div>
                <div class="text-xs opacity-60">Last 7 days · {{ $s['profile_views_30d'] }} / 30d</div>
            </div>
        </div>
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="text-sm opacity-70">New followers</div>
                <div class="text-2xl font-semibold">{{ $s['new_followers_7d'] }}</div>
                <div class="text-xs opacity-60">Last 7 days · {{ $s['new_followers_30d'] }} / 30d</div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Top posts (7 days)</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->topPosts as $post)
                    <a class="flex items-start justify-between gap-3 hover:bg-base-200 rounded-box px-2 py-2" href="{{ route('posts.show', $post) }}" wire:navigate>
                        <div class="min-w-0">
                            <div class="truncate">{{ $post->body }}</div>
                            <div class="text-xs opacity-60">
                                {{ $post->analytics_views_7d }} views · {{ $post->likes_count }} likes · {{ $post->reposts_count }} reposts · {{ $post->replies_count }} replies
                            </div>
                        </div>
                        <div class="text-sm opacity-60 shrink-0">{{ $post->created_at->diffForHumans() }}</div>
                    </a>
                @empty
                    <div class="opacity-70 text-sm">No views yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

