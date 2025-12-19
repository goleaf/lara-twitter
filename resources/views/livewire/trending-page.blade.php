<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="text-xl font-semibold">Trending</div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <input
                        class="input input-bordered input-sm w-full sm:w-48"
                        type="text"
                        placeholder="Location (optional)"
                        wire:model.live.debounce.400ms="loc"
                    />
                    <a class="btn btn-ghost btn-sm" href="{{ route('trending', ['tab' => $tab, 'loc' => '']) }}" wire:navigate>Global</a>
                </div>
            </div>

            <div class="tabs tabs-boxed mt-4">
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
                    <div class="font-semibold">Trending keywords (24h)</div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->trendingKeywords as $row)
                            <a class="flex items-center justify-between hover:bg-base-200/70 transition rounded-box px-2 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:navigate>
                                <div class="min-w-0">
                                    <div class="font-medium truncate">{{ $row['keyword'] }}</div>
                                    <div class="text-xs opacity-70">
                                        {{ $row['recent_count'] ?? 0 }} in last hour
                                        · {{ $row['users_count'] ?? 0 }} people
                                    </div>
                                </div>
                                <div class="text-sm opacity-60">{{ $row['count'] }}</div>
                            </a>
                        @empty
                            <div class="opacity-70 text-sm">No keywords yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @elseif ($tab === 'topics')
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Trending topics (24h)</div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->trendingTopics as $row)
                            <a class="flex items-center justify-between hover:bg-base-200/70 transition rounded-box px-2 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('explore', ['tab' => $row['category']]) }}" wire:navigate>
                                <div class="min-w-0">
                                    <div class="font-medium truncate">{{ $row['topic'] }}</div>
                                    <div class="text-xs opacity-70">{{ $row['recent_count'] ?? 0 }} in last hour</div>
                                </div>
                                <div class="text-sm opacity-60">{{ $row['count'] }}</div>
                            </a>
                        @empty
                            <div class="opacity-70 text-sm">No topics yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @elseif ($tab === 'conversations')
            <div class="space-y-3">
                @forelse ($this->trendingConversations as $post)
                    <livewire:post-card :post="$post" :key="$post->id" />
                @empty
                    <div class="card bg-base-100 border">
                        <div class="card-body">
                            <div class="opacity-70 text-sm">No conversations yet.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Trending hashtags (24h)</div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->trendingHashtags as $tag)
                            <div class="flex items-start justify-between gap-2 hover:bg-base-200/70 transition rounded-box px-2 py-2 focus-within:ring-2 focus-within:ring-primary/20">
                                <a class="min-w-0 focus:outline-none" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
                                    <div class="font-medium truncate">#{{ $tag->tag }}</div>
                                    <div class="text-xs opacity-70">
                                        {{ (int) ($tag->users_count ?? 0) }} people talking
                                        @if ((int) ($tag->recent_uses_count ?? 0) > 0)
                                            · {{ (int) $tag->recent_uses_count }} posts in last hour
                                        @endif
                                    </div>
                                </a>

                                <div class="flex items-center gap-2">
                                    <div class="text-sm opacity-60">{{ $tag->uses_count }}</div>
                                    <livewire:report-button :reportable-type="\App\Models\Hashtag::class" :reportable-id="$tag->id" label="Report" :key="'report-tag-'.$tag->id" />
                                </div>
                            </div>
                        @empty
                            <div class="opacity-70 text-sm">No hashtags yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
