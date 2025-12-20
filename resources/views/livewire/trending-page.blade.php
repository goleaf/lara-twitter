<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @php($locationLabel = trim($loc) !== '' ? $loc : 'Global')

    <div class="card bg-base-100 border">
        <div class="card-body gap-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <div class="text-xl font-semibold">Trending</div>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-base-content/60">
                        <span class="badge badge-ghost badge-sm">Last 24h</span>
                        <span class="badge badge-ghost badge-sm">Updates every minute</span>
                        <span class="badge badge-outline badge-sm">Location: {{ $locationLabel }}</span>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <input
                        class="input input-bordered input-sm w-full sm:w-56"
                        type="text"
                        placeholder="Location (optional)"
                        wire:model.live.debounce.400ms="loc"
                    />
                    <a class="btn btn-ghost btn-sm" href="{{ route('trending', ['tab' => $tab, 'loc' => '']) }}" wire:navigate>Global</a>
                </div>
            </div>

            <div class="tabs tabs-boxed">
                <a class="tab {{ $tab === 'hashtags' ? 'tab-active' : '' }}" href="{{ route('trending', ['tab' => 'hashtags', 'loc' => $loc]) }}" wire:navigate>Hashtags</a>
                <a class="tab {{ $tab === 'keywords' ? 'tab-active' : '' }}" href="{{ route('trending', ['tab' => 'keywords', 'loc' => $loc]) }}" wire:navigate>Keywords</a>
                <a class="tab {{ $tab === 'topics' ? 'tab-active' : '' }}" href="{{ route('trending', ['tab' => 'topics', 'loc' => $loc]) }}" wire:navigate>Topics</a>
                <a class="tab {{ $tab === 'conversations' ? 'tab-active' : '' }}" href="{{ route('trending', ['tab' => 'conversations', 'loc' => $loc]) }}" wire:navigate>Conversations</a>
            </div>
        </div>
    </div>

    <div wire:poll.visible.60s>
        @if ($tab === 'keywords')
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">Trending keywords</div>
                        <div class="text-xs text-base-content/60">Last 24h</div>
                    </div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->trendingKeywords as $row)
                            <x-list-row href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:key="trending-keyword-{{ $row['keyword'] }}" wire:navigate>
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="text-xs font-semibold text-base-content/40 w-6 text-right tabular-nums">{{ $loop->iteration }}</div>
                                    <div class="min-w-0">
                                        <div class="font-medium truncate">{{ $row['keyword'] }}</div>
                                        <div class="text-xs opacity-70">
                                            {{ $row['recent_count'] ?? 0 }} in last hour
                                            · {{ $row['users_count'] ?? 0 }} people
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold tabular-nums">{{ $row['count'] }}</div>
                                </div>
                            </x-list-row>
                        @empty
                            <x-empty-state>
                                No keywords yet.
                            </x-empty-state>
                        @endforelse
                    </div>
                </div>
            </div>
        @elseif ($tab === 'topics')
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">Trending topics</div>
                        <div class="text-xs text-base-content/60">Last 24h</div>
                    </div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->trendingTopics as $row)
                            <x-list-row href="{{ route('explore', ['tab' => $row['category']]) }}" wire:key="trending-topic-{{ $row['category'] }}" wire:navigate>
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="text-xs font-semibold text-base-content/40 w-6 text-right tabular-nums">{{ $loop->iteration }}</div>
                                    <div class="min-w-0">
                                        <div class="font-medium truncate">{{ $row['topic'] }}</div>
                                        <div class="text-xs opacity-70">{{ $row['recent_count'] ?? 0 }} in last hour</div>
                                    </div>
                                </div>
                                <div class="text-right text-sm font-semibold tabular-nums">{{ $row['count'] }}</div>
                            </x-list-row>
                        @empty
                            <x-empty-state>
                                No topics yet.
                            </x-empty-state>
                        @endforelse
                    </div>
                </div>
            </div>
        @elseif ($tab === 'conversations')
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs text-base-content/60">
                    <div class="font-semibold text-base-content">Trending conversations</div>
                    <div>Last 24h</div>
                </div>
                @forelse ($this->trendingConversations as $post)
                    <livewire:post-card :post="$post" :key="$post->id" />
                @empty
                    <x-empty-state>
                        No conversations yet.
                    </x-empty-state>
                @endforelse
            </div>
        @else
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">Trending hashtags</div>
                        <div class="text-xs text-base-content/60">Last 24h</div>
                    </div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->trendingHashtags as $tag)
                            <x-list-row class="items-start" wire:key="trending-hashtag-{{ $tag->id }}">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="text-xs font-semibold text-base-content/40 w-6 text-right tabular-nums">{{ $loop->iteration }}</div>
                                    <a class="min-w-0 focus:outline-none" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
                                        <div class="font-medium truncate">#{{ $tag->tag }}</div>
                                        <div class="text-xs opacity-70">
                                            {{ (int) ($tag->users_count ?? 0) }} people talking
                                            @if ((int) ($tag->recent_uses_count ?? 0) > 0)
                                                · {{ (int) $tag->recent_uses_count }} posts in last hour
                                            @endif
                                        </div>
                                    </a>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-semibold tabular-nums">{{ $tag->uses_count }}</div>
                                    <livewire:report-button :reportable-type="\App\Models\Hashtag::class" :reportable-id="$tag->id" label="Report" :key="'report-tag-'.$tag->id" />
                                </div>
                            </x-list-row>
                        @empty
                            <x-empty-state>
                                No hashtags yet.
                            </x-empty-state>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
