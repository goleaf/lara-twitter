@php($s = $this->summary)

<div class="max-w-3xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-xl font-semibold">Analytics</div>
                    <div class="text-sm opacity-70 pt-1">
                        Views and clicks are tracked as unique daily events (not total impressions).
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="text-sm opacity-70">Range</div>
                    <select class="select select-bordered select-sm" wire:model.live="range">
                        <option value="7d">7 days</option>
                        <option value="28d">28 days</option>
                        <option value="90d">90 days</option>
                    </select>
                </div>
            </div>

            <div class="tabs tabs-boxed mt-4">
                <a class="tab {{ $tab === 'overview' ? 'tab-active' : '' }}" href="{{ route('analytics', ['tab' => 'overview', 'range' => $range]) }}" wire:navigate>Overview</a>
                <a class="tab {{ $tab === 'tweets' ? 'tab-active' : '' }}" href="{{ route('analytics', ['tab' => 'tweets', 'range' => $range, 'sort' => $sort, 'dir' => $dir]) }}" wire:navigate>Tweets</a>
                <a class="tab {{ $tab === 'audience' ? 'tab-active' : '' }}" href="{{ route('analytics', ['tab' => 'audience', 'range' => $range]) }}" wire:navigate>Audience</a>
            </div>
        </div>
    </div>

    @if ($tab === 'tweets')
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="flex items-center justify-between gap-4">
                    <div class="font-semibold">Tweets ({{ $s['days'] }} days)</div>
                    <div class="flex items-center gap-2">
                        <select class="select select-bordered select-sm" wire:model.live="sort">
                            <option value="date">Date</option>
                            <option value="impressions">Impressions</option>
                            <option value="engagements">Engagements</option>
                            <option value="engagement_rate">Engagement rate</option>
                            <option value="link_clicks">Link clicks</option>
                            <option value="profile_clicks">Profile clicks</option>
                            <option value="media_views">Media views</option>
                            <option value="likes">Likes</option>
                            <option value="reposts">Reposts</option>
                            <option value="replies">Replies</option>
                        </select>
                        <select class="select select-bordered select-sm" wire:model.live="dir">
                            <option value="desc">Desc</option>
                            <option value="asc">Asc</option>
                        </select>
                        <a class="btn btn-ghost btn-sm" href="{{ route('analytics.export', ['range' => $range]) }}">Export CSV</a>
                    </div>
                </div>

                <div class="overflow-x-auto pt-3">
                    <table class="table table-zebra table-sm">
                        <thead>
                            <tr>
                                <th>Post</th>
                                <th class="text-right">Impressions</th>
                                <th class="text-right">Engagements</th>
                                <th class="text-right">Rate</th>
                                <th class="text-right">Links</th>
                                <th class="text-right">Profile</th>
                                <th class="text-right">Media</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->tweetRows as $post)
                                <tr>
                                    <td class="min-w-[16rem] max-w-md">
                                        <a class="link link-hover" href="{{ route('posts.show', $post) }}" wire:navigate>
                                            {{ \Illuminate\Support\Str::limit($post->body, 120) }}
                                        </a>
                                        <div class="text-xs opacity-60">{{ $post->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="text-right">{{ $post->analytics_impressions }}</td>
                                    <td class="text-right">{{ $post->analytics_engagements }}</td>
                                    <td class="text-right">{{ number_format($post->analytics_engagement_rate * 100, 1) }}%</td>
                                    <td class="text-right">{{ $post->analytics_link_clicks }}</td>
                                    <td class="text-right">{{ $post->analytics_profile_clicks }}</td>
                                    <td class="text-right">{{ $post->analytics_media_views }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="opacity-70">No posts in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-xs opacity-60 pt-2">Shows up to 100 posts published in the selected range.</div>
            </div>
        </div>
    @elseif ($tab === 'audience')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Follower growth ({{ $s['days'] }} days)</div>
                    <div class="space-y-1 pt-2">
                        @forelse ($this->followerGrowth as $row)
                            <div class="flex items-center justify-between">
                                <div class="text-sm">{{ $row->day }}</div>
                                <div class="text-sm opacity-70">{{ $row->followers }}</div>
                            </div>
                        @empty
                            <div class="opacity-70 text-sm">No new followers yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Top follower locations</div>
                    <div class="space-y-1 pt-2">
                        @forelse ($this->topFollowerLocations as $row)
                            <div class="flex items-center justify-between">
                                <div class="text-sm truncate">{{ $row->location }}</div>
                                <div class="text-sm opacity-70">{{ $row->followers }}</div>
                            </div>
                        @empty
                            <div class="opacity-70 text-sm">No locations yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Followers also follow</div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->alsoFollowedAccounts as $row)
                            <a class="flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('profile.show', ['user' => $row->username]) }}" wire:navigate>
                                <div class="min-w-0">
                                    <div class="font-medium truncate">{{ $row->name }}</div>
                                    <div class="text-xs opacity-60 truncate">&#64;{{ $row->username }}</div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0 tabular-nums">{{ $row->followers }}</div>
                            </a>
                        @empty
                            <div class="opacity-70 text-sm">Not enough data yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Impressions</div>
                    <div class="text-2xl font-semibold">{{ $s['impressions'] }}</div>
                    <div class="text-xs opacity-60">Unique daily views · {{ $s['days'] }} days</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['impressions_delta'] >= 0 ? '+' : '' }}{{ $s['impressions_delta'] }}
                        @if ($s['impressions_delta_pct'] !== null)
                            ({{ number_format($s['impressions_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Engagements</div>
                    <div class="text-2xl font-semibold">{{ $s['engagements'] }}</div>
                    <div class="text-xs opacity-60">Likes, reposts, replies, clicks</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['engagements_delta'] >= 0 ? '+' : '' }}{{ $s['engagements_delta'] }}
                        @if ($s['engagements_delta_pct'] !== null)
                            ({{ number_format($s['engagements_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Engagement rate</div>
                    <div class="text-2xl font-semibold">{{ number_format($s['engagement_rate'] * 100, 1) }}%</div>
                    <div class="text-xs opacity-60">Engagements / impressions</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['engagement_rate_delta'] >= 0 ? '+' : '' }}{{ number_format($s['engagement_rate_delta'] * 100, 1) }} pp
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Profile visits</div>
                    <div class="text-2xl font-semibold">{{ $s['profile_visits'] }}</div>
                    <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['profile_visits_delta'] >= 0 ? '+' : '' }}{{ $s['profile_visits_delta'] }}
                        @if ($s['profile_visits_delta_pct'] !== null)
                            ({{ number_format($s['profile_visits_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Mentions</div>
                    <div class="text-2xl font-semibold">{{ $s['mentions'] }}</div>
                    <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['mentions_delta'] >= 0 ? '+' : '' }}{{ $s['mentions_delta'] }}
                        @if ($s['mentions_delta_pct'] !== null)
                            ({{ number_format($s['mentions_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">New followers</div>
                    <div class="text-2xl font-semibold">{{ $s['new_followers'] }}</div>
                    <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['new_followers_delta'] >= 0 ? '+' : '' }}{{ $s['new_followers_delta'] }}
                        @if ($s['new_followers_delta_pct'] !== null)
                            ({{ number_format($s['new_followers_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Posts published</div>
                    <div class="text-2xl font-semibold">{{ $s['posts_published'] }}</div>
                    <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['posts_published_delta'] >= 0 ? '+' : '' }}{{ $s['posts_published_delta'] }}
                        @if ($s['posts_published_delta_pct'] !== null)
                            ({{ number_format($s['posts_published_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Link clicks</div>
                    <div class="text-2xl font-semibold">{{ $s['link_clicks'] }}</div>
                    <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['link_clicks_delta'] >= 0 ? '+' : '' }}{{ $s['link_clicks_delta'] }}
                        @if ($s['link_clicks_delta_pct'] !== null)
                            ({{ number_format($s['link_clicks_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Profile clicks</div>
                    <div class="text-2xl font-semibold">{{ $s['profile_clicks'] }}</div>
                    <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['profile_clicks_delta'] >= 0 ? '+' : '' }}{{ $s['profile_clicks_delta'] }}
                        @if ($s['profile_clicks_delta_pct'] !== null)
                            ({{ number_format($s['profile_clicks_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="text-sm opacity-70">Media views</div>
                    <div class="text-2xl font-semibold">{{ $s['media_views'] }}</div>
                    <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                    <div class="text-xs opacity-60">
                        vs prev: {{ $s['media_views_delta'] >= 0 ? '+' : '' }}{{ $s['media_views_delta'] }}
                        @if ($s['media_views_delta_pct'] !== null)
                            ({{ number_format($s['media_views_delta_pct'] * 100, 1) }}%)
                        @endif
                    </div>
                </div>
            </div>
        </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Top posts ({{ $s['days'] }} days)</div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->topPosts as $post)
                            <a class="flex items-start justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('posts.show', $post) }}" wire:navigate>
                                <div class="min-w-0">
                                    <div class="font-medium truncate">{{ $post->body }}</div>
                                    <div class="text-xs opacity-60">
                                        {{ $post->analytics_impressions }} impressions ·
                                        {{ $post->analytics_engagements }} engagements ·
                                        {{ number_format($post->analytics_engagement_rate * 100, 1) }}% ·
                                        {{ $post->analytics_link_clicks }} links ·
                                        {{ $post->analytics_profile_clicks }} profile ·
                                        {{ $post->analytics_media_views }} media
                                    </div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0 whitespace-nowrap">{{ $post->created_at->diffForHumans() }}</div>
                            </a>
                        @empty
                            <div class="opacity-70 text-sm">No impressions yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
    @endif
</div>
