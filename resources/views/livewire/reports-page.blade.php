<div class="max-w-3xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-xl font-semibold">Reports</div>
                    <div class="text-sm opacity-70">Track the status of reports you’ve submitted.</div>
                </div>

                <label class="flex items-center gap-2">
                    <span class="text-sm opacity-70">Status</span>
                    <select wire:model.live="status" class="select select-bordered select-sm">
                        <option value="all">All</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>Case</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Target</th>
                            <th class="text-right">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->reports as $report)
                            @php($target = $report->reportable)
                            <tr>
                                <td class="font-mono text-xs whitespace-nowrap">{{ $report->case_number }}</td>

                                <td>
                                    @php($badge = match ($report->status) { 'open' => 'badge-neutral', 'reviewing' => 'badge-warning', 'resolved' => 'badge-success', 'dismissed' => 'badge-ghost', default => 'badge-neutral' })
                                    <span class="badge badge-sm {{ $badge }} whitespace-nowrap">{{ ucfirst($report->status) }}</span>
                                </td>

                                <td class="text-sm">{{ \App\Models\Report::reasonLabel($report->reason) }}</td>

                                <td class="min-w-0">
                                    @if (! $target)
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="badge badge-ghost badge-sm shrink-0">{{ class_basename($report->reportable_type) }}</span>
                                            <span class="opacity-70 text-sm truncate">#{{ $report->reportable_id }} (deleted)</span>
                                        </div>
                                    @elseif ($target instanceof \App\Models\Post)
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="badge badge-ghost badge-sm shrink-0">Post</span>
                                            <a class="link link-hover block truncate" href="{{ route('posts.show', $target) }}" wire:navigate>
                                                {{ str($target->body)->limit(60) }}
                                            </a>
                                        </div>
                                    @elseif ($target instanceof \App\Models\User)
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="badge badge-ghost badge-sm shrink-0">Account</span>
                                            <a class="link link-hover block truncate" href="{{ route('profile.show', ['user' => $target]) }}" wire:navigate>
                                                &#64;{{ $target->username }}
                                            </a>
                                        </div>
                                    @elseif ($target instanceof \App\Models\Hashtag)
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="badge badge-ghost badge-sm shrink-0">Hashtag</span>
                                            <a class="link link-hover block truncate" href="{{ route('hashtags.show', ['tag' => $target->tag]) }}" wire:navigate>
                                                #{{ $target->tag }}
                                            </a>
                                        </div>
                                    @elseif ($target instanceof \App\Models\Message)
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="badge badge-ghost badge-sm shrink-0">Message</span>
                                            <a class="link link-hover block truncate" href="{{ route('messages.show', $target->conversation_id) }}" wire:navigate>
                                                {{ $target->body ? str($target->body)->limit(60) : 'Attachment' }}
                                            </a>
                                        </div>
                                    @elseif ($target instanceof \App\Models\UserList)
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="badge badge-ghost badge-sm shrink-0">List</span>
                                            <a class="link link-hover block truncate" href="{{ route('lists.show', $target) }}" wire:navigate>
                                                {{ $target->name }}
                                            </a>
                                        </div>
                                    @elseif ($target instanceof \App\Models\Space)
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="badge badge-ghost badge-sm shrink-0">Space</span>
                                            <a class="link link-hover block truncate" href="{{ route('spaces.show', $target) }}" wire:navigate>
                                                {{ $target->title }}
                                            </a>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="badge badge-ghost badge-sm shrink-0">{{ class_basename($report->reportable_type) }}</span>
                                            <span class="opacity-70 text-sm truncate">#{{ $report->reportable_id }}</span>
                                        </div>
                                    @endif
                                </td>

                                <td class="text-right text-sm opacity-70 whitespace-nowrap">
                                    {{ $report->updated_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <x-table-empty colspan="5">
                                No reports yet.
                            </x-table-empty>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="pt-2">
        {{ $this->reports->links() }}
    </div>
</div>
