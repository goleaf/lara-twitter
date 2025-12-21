@php($s = $this->summary)

<div class="max-w-6xl mx-auto space-y-4">
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
        @php
            $avgImpressionsPerPost = $s['posts_published'] > 0 ? (int) round($s['impressions'] / $s['posts_published']) : 0;
            $avgEngagementsPerPost = $s['posts_published'] > 0 ? round($s['engagements'] / $s['posts_published'], 1) : 0;
        @endphp

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

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4 text-sm">
                    <div class="rounded-box border border-base-200/70 bg-base-200/40 p-3">
                        <div class="text-xs uppercase tracking-[0.3em] opacity-60">Posts</div>
                        <div class="text-lg font-semibold tabular-nums">{{ $s['posts_published'] }}</div>
                        <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                    </div>
                    <div class="rounded-box border border-base-200/70 bg-base-200/40 p-3">
                        <div class="text-xs uppercase tracking-[0.3em] opacity-60">Avg impressions</div>
                        <div class="text-lg font-semibold tabular-nums">{{ $avgImpressionsPerPost }}</div>
                        <div class="text-xs opacity-60">per post</div>
                    </div>
                    <div class="rounded-box border border-base-200/70 bg-base-200/40 p-3">
                        <div class="text-xs uppercase tracking-[0.3em] opacity-60">Avg engagements</div>
                        <div class="text-lg font-semibold tabular-nums">{{ $avgEngagementsPerPost }}</div>
                        <div class="text-xs opacity-60">per post</div>
                    </div>
                    <div class="rounded-box border border-base-200/70 bg-base-200/40 p-3">
                        <div class="text-xs uppercase tracking-[0.3em] opacity-60">Engagement rate</div>
                        <div class="text-lg font-semibold tabular-nums">{{ number_format($s['engagement_rate'] * 100, 1) }}%</div>
                        <div class="text-xs opacity-60">range avg</div>
                    </div>
                </div>

                <div class="pt-3">
                    <div class="overflow-x-auto rounded-box border border-base-200">
                        <table class="table table-zebra table-sm">
                            <thead>
                                <tr>
                                    <th>Post</th>
                                    <th class="text-right tabular-nums">Impressions</th>
                                    <th class="text-right tabular-nums">Engagements</th>
                                    <th class="text-right tabular-nums">Rate</th>
                                    <th class="text-right tabular-nums">Links</th>
                                    <th class="text-right tabular-nums">Profile</th>
                                    <th class="text-right tabular-nums">Media</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->tweetRows as $post)
                                    <tr wire:key="analytics-post-{{ $post->id }}">
                                        <td class="min-w-[16rem] max-w-md">
                                            <a class="link link-hover" href="{{ route('posts.show', $post) }}" wire:navigate>
                                                {{ \Illuminate\Support\Str::limit($post->body, 120) }}
                                            </a>
                                            <div class="text-xs opacity-60">{{ $post->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="text-right tabular-nums">{{ $post->analytics_impressions }}</td>
                                        <td class="text-right tabular-nums">{{ $post->analytics_engagements }}</td>
                                        <td class="text-right tabular-nums">{{ number_format($post->analytics_engagement_rate * 100, 1) }}%</td>
                                        <td class="text-right tabular-nums">{{ $post->analytics_link_clicks }}</td>
                                        <td class="text-right tabular-nums">{{ $post->analytics_profile_clicks }}</td>
                                        <td class="text-right tabular-nums">{{ $post->analytics_media_views }}</td>
                                    </tr>
                                @empty
                                    <x-table-empty colspan="7">
                                        No posts in this range.
                                    </x-table-empty>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-xs opacity-60 pt-2">Shows up to 100 posts published in the selected range.</div>
            </div>
        </div>
    @elseif ($tab === 'audience')
        @php
            $growthSeries = $this->followerGrowthSeries;
            $growthValues = $growthSeries['values'];
            $growthMax = max($growthValues ?: [0]);
            $growthTotal = array_sum($growthValues);
            $avgDailyFollowers = $s['days'] > 0 ? round($s['new_followers'] / $s['days'], 1) : 0;
            $peakIndex = $growthMax > 0 ? array_search($growthMax, $growthValues, true) : false;
            $peakDay = $peakIndex !== false ? ($growthSeries['days'][$peakIndex] ?? null) : null;
            $topLocations = $this->topFollowerLocations;
            $alsoFollowed = $this->alsoFollowedAccounts;
            $maxLocationFollowers = max($topLocations->pluck('followers')->all() ?: [0]);
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="card bg-base-100 border lg:col-span-2">
                <div class="card-body">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="font-semibold">Follower growth</div>
                            <div class="text-xs opacity-60">{{ $s['days'] }} days</div>
                        </div>
                        <div class="text-xs opacity-60 text-right">
                            <div>Total: <span class="font-medium tabular-nums">{{ $s['new_followers'] }}</span></div>
                            <div>Avg/day: <span class="font-medium tabular-nums">{{ $avgDailyFollowers }}</span></div>
                            @if ($peakDay)
                                <div>Peak: <span class="font-medium">{{ $peakDay }}</span></div>
                            @endif
                        </div>
                    </div>

                    @if ($growthTotal === 0)
                        <x-empty-state class="mt-4 px-3 py-6">
                            No new followers yet.
                        </x-empty-state>
                    @else
                        <div class="mt-4 rounded-box border border-base-200 bg-base-200/40 px-2 py-3">
                            <div class="flex items-end gap-0.5 h-24">
                                @foreach ($growthValues as $index => $value)
                                    @php($height = $growthMax > 0 ? max(4, (int) round(($value / $growthMax) * 100)) : 0)
                                    <div class="flex-1 rounded-t bg-primary/60" style="height: {{ $height }}%" title="{{ $growthSeries['days'][$index] }}: {{ $value }}"></div>
                                @endforeach
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[0.65rem] uppercase tracking-[0.2em] opacity-60">
                                <span>{{ $growthSeries['days'][0] ?? '' }}</span>
                                <span>{{ $growthSeries['days'][count($growthSeries['days']) - 1] ?? '' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Audience signals</div>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Profile visits</span>
                            <span class="font-semibold tabular-nums">{{ $s['profile_visits'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">New followers</span>
                            <span class="font-semibold tabular-nums">{{ $s['new_followers'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Follower conversion</span>
                            <span class="font-semibold tabular-nums">{{ number_format(($s['profile_visits'] > 0 ? $s['new_followers'] / $s['profile_visits'] : 0) * 100, 2) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Mentions</span>
                            <span class="font-semibold tabular-nums">{{ $s['mentions'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Posts published</span>
                            <span class="font-semibold tabular-nums">{{ $s['posts_published'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Top follower locations</div>
                    <div class="space-y-3 pt-4">
                        @forelse ($topLocations as $row)
                            @php($percent = $maxLocationFollowers > 0 ? (int) round(($row->followers / $maxLocationFollowers) * 100) : 0)
                            <div class="space-y-1" wire:key="follower-location-{{ md5($row->location) }}">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="truncate">{{ $row->location }}</div>
                                    <div class="text-sm opacity-70 tabular-nums">{{ $row->followers }}</div>
                                </div>
                                <div class="h-1.5 rounded-full bg-base-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-secondary/60" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <x-empty-state class="px-3 py-2">
                                No locations yet.
                            </x-empty-state>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Followers also follow</div>
                    <div class="space-y-2 pt-2">
                        @forelse ($alsoFollowed as $row)
                            <x-list-row href="{{ route('profile.show', ['user' => $row->username]) }}" wire:navigate wire:key="also-followed-{{ $row->username }}">
                                <div class="min-w-0">
                                    <div class="font-medium truncate">{{ $row->name }}</div>
                                    <div class="text-xs opacity-60 truncate">&#64;{{ $row->username }}</div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0 tabular-nums">{{ $row->followers }}</div>
                            </x-list-row>
                        @empty
                            <x-empty-state class="px-3 py-2">
                                Not enough data yet.
                            </x-empty-state>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @else
        @php
            $series = $this->overviewSeries;
            $daysSeries = $series['days'];
            $impressionsSeries = $series['impressions'];
            $profileSeries = $series['profile_visits'];
            $maxImpressions = max($impressionsSeries ?: [0]);
            $maxProfile = max($profileSeries ?: [0]);
            $startDay = $daysSeries[0] ?? '';
            $endDay = $daysSeries ? $daysSeries[count($daysSeries) - 1] : '';
            $avgDailyImpressions = $s['days'] > 0 ? (int) round($s['impressions'] / $s['days']) : 0;
            $avgDailyProfile = $s['days'] > 0 ? (int) round($s['profile_visits'] / $s['days']) : 0;
            $avgImpressionsPerPost = $s['posts_published'] > 0 ? (int) round($s['impressions'] / $s['posts_published']) : 0;
            $avgEngagementsPerPost = $s['posts_published'] > 0 ? round($s['engagements'] / $s['posts_published'], 1) : 0;
            $clickThroughRate = $s['impressions'] > 0 ? $s['link_clicks'] / $s['impressions'] : 0;
            $profileClickRate = $s['impressions'] > 0 ? $s['profile_clicks'] / $s['impressions'] : 0;
            $mediaViewRate = $s['impressions'] > 0 ? $s['media_views'] / $s['impressions'] : 0;
            $followerConversion = $s['profile_visits'] > 0 ? $s['new_followers'] / $s['profile_visits'] : 0;
            $engagementTotal = $s['engagements'] > 0 ? $s['engagements'] : 0;
            $engagementMix = [
                ['label' => 'Likes', 'value' => $s['likes'], 'color' => 'bg-primary/60'],
                ['label' => 'Reposts', 'value' => $s['reposts'], 'color' => 'bg-secondary/60'],
                ['label' => 'Replies', 'value' => $s['replies'], 'color' => 'bg-accent/60'],
                ['label' => 'Link clicks', 'value' => $s['link_clicks'], 'color' => 'bg-info/60'],
                ['label' => 'Profile clicks', 'value' => $s['profile_clicks'], 'color' => 'bg-success/60'],
                ['label' => 'Media views', 'value' => $s['media_views'], 'color' => 'bg-warning/60'],
            ];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="card bg-base-100 border lg:col-span-2">
                <div class="card-body space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold">Performance snapshot</div>
                            <div class="text-xs opacity-60">{{ $s['days'] }} days rolling</div>
                        </div>
                        <div class="text-xs uppercase tracking-[0.3em] opacity-60">Trends</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <div class="font-medium">Impressions</div>
                                <div class="text-xs opacity-60">avg {{ number_format($avgDailyImpressions) }}/day</div>
                            </div>
                            <div class="mt-2 rounded-box border border-base-200 bg-base-200/40 px-2 py-3">
                                <div class="flex items-end gap-0.5 h-24">
                                    @foreach ($impressionsSeries as $index => $value)
                                        @php($height = $maxImpressions > 0 ? max(4, (int) round(($value / $maxImpressions) * 100)) : 0)
                                        <div class="flex-1 rounded-t bg-primary/60" style="height: {{ $height }}%" title="{{ $daysSeries[$index] ?? '' }}: {{ $value }}"></div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-[0.65rem] uppercase tracking-[0.2em] opacity-60">
                                <span>{{ $startDay }}</span>
                                <span>{{ $endDay }}</span>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <div class="font-medium">Profile visits</div>
                                <div class="text-xs opacity-60">avg {{ number_format($avgDailyProfile) }}/day</div>
                            </div>
                            <div class="mt-2 rounded-box border border-base-200 bg-base-200/40 px-2 py-3">
                                <div class="flex items-end gap-0.5 h-24">
                                    @foreach ($profileSeries as $index => $value)
                                        @php($height = $maxProfile > 0 ? max(4, (int) round(($value / $maxProfile) * 100)) : 0)
                                        <div class="flex-1 rounded-t bg-secondary/60" style="height: {{ $height }}%" title="{{ $daysSeries[$index] ?? '' }}: {{ $value }}"></div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-[0.65rem] uppercase tracking-[0.2em] opacity-60">
                                <span>{{ $startDay }}</span>
                                <span>{{ $endDay }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Rates and averages</div>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Avg impressions per post</span>
                            <span class="font-semibold tabular-nums">{{ $avgImpressionsPerPost }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Avg engagements per post</span>
                            <span class="font-semibold tabular-nums">{{ $avgEngagementsPerPost }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Link click-through</span>
                            <span class="font-semibold tabular-nums">{{ number_format($clickThroughRate * 100, 2) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Profile click rate</span>
                            <span class="font-semibold tabular-nums">{{ number_format($profileClickRate * 100, 2) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Media view rate</span>
                            <span class="font-semibold tabular-nums">{{ number_format($mediaViewRate * 100, 2) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-70">Follower conversion</span>
                            <span class="font-semibold tabular-nums">{{ number_format($followerConversion * 100, 2) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Engagement mix</div>
                <div class="text-xs opacity-60">Share of total engagements ({{ $s['engagements'] }})</div>
                <div class="mt-4 h-2 rounded-full bg-base-200 overflow-hidden">
                    @foreach ($engagementMix as $item)
                        @php($percent = $engagementTotal > 0 ? ($item['value'] / $engagementTotal) * 100 : 0)
                        <div class="h-full {{ $item['color'] }}" style="width: {{ $percent }}%"></div>
                    @endforeach
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                    @foreach ($engagementMix as $item)
                        @php($percent = $engagementTotal > 0 ? ($item['value'] / $engagementTotal) * 100 : 0)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full {{ $item['color'] }}"></span>
                                <span>{{ $item['label'] }}</span>
                            </div>
                            <span class="tabular-nums">{{ $item['value'] }} ({{ number_format($percent, 1) }}%)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 tabular-nums">
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
                    @php($topPosts = $this->topPosts)
                    @php($maxTopImpressions = $topPosts->max('analytics_impressions') ?? 0)
                    @forelse ($topPosts as $post)
                        <x-list-row href="{{ route('posts.show', $post) }}" wire:navigate class="items-start" wire:key="top-post-{{ $post->id }}">
                            <div class="min-w-0">
                                <div class="font-medium truncate">{{ $post->body }}</div>
                                <div class="text-xs opacity-60 tabular-nums">
                                    {{ $post->analytics_impressions }} impressions ·
                                    {{ $post->analytics_engagements }} engagements ·
                                    {{ number_format($post->analytics_engagement_rate * 100, 1) }}% ·
                                    {{ $post->analytics_link_clicks }} links ·
                                    {{ $post->analytics_profile_clicks }} profile ·
                                    {{ $post->analytics_media_views }} media
                                </div>
                                <div class="mt-2 h-1.5 rounded-full bg-base-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-primary/60" style="width: {{ $maxTopImpressions > 0 ? (int) round(($post->analytics_impressions / $maxTopImpressions) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0 whitespace-nowrap">{{ $post->created_at->diffForHumans() }}</div>
                        </x-list-row>
                    @empty
                        <x-empty-state class="px-3 py-2">
                            No impressions yet.
                        </x-empty-state>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
