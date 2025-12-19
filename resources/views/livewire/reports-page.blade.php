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

    <div class="card bg-base-100 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
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
                                <td class="font-mono text-xs">{{ $report->case_number }}</td>

                                <td>
                                    @php($badge = match ($report->status) { 'open' => 'badge-neutral', 'reviewing' => 'badge-warning', 'resolved' => 'badge-success', 'dismissed' => 'badge-ghost', default => 'badge-neutral' })
                                    <span class="badge {{ $badge }}">{{ $report->status }}</span>
                                </td>

                                <td>{{ \App\Models\Report::reasonLabel($report->reason) }}</td>

                                <td class="min-w-0">
                                    @if (! $target)
                                        <span class="opacity-70">{{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }} (deleted)</span>
                                    @elseif ($target instanceof \App\Models\Post)
                                        <a class="link link-hover truncate inline-block max-w-xs" href="{{ route('posts.show', $target) }}" wire:navigate>
                                            Post: {{ str($target->body)->limit(60) }}
                                        </a>
                                    @elseif ($target instanceof \App\Models\User)
                                        <a class="link link-hover" href="{{ route('profile.show', ['user' => $target]) }}" wire:navigate>
                                            Account: &#64;{{ $target->username }}
                                        </a>
                                    @elseif ($target instanceof \App\Models\Hashtag)
                                        <a class="link link-hover" href="{{ route('hashtags.show', ['tag' => $target->tag]) }}" wire:navigate>
                                            Hashtag: #{{ $target->tag }}
                                        </a>
                                    @elseif ($target instanceof \App\Models\Message)
                                        <a class="link link-hover truncate inline-block max-w-xs" href="{{ route('messages.show', $target->conversation_id) }}" wire:navigate>
                                            Message: {{ $target->body ? str($target->body)->limit(60) : 'Attachment' }}
                                        </a>
                                    @elseif ($target instanceof \App\Models\UserList)
                                        <a class="link link-hover truncate inline-block max-w-xs" href="{{ route('lists.show', $target) }}" wire:navigate>
                                            List: {{ $target->name }}
                                        </a>
                                    @elseif ($target instanceof \App\Models\Space)
                                        <a class="link link-hover truncate inline-block max-w-xs" href="{{ route('spaces.show', $target) }}" wire:navigate>
                                            Space: {{ $target->title }}
                                        </a>
                                    @else
                                        <span class="opacity-70">{{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}</span>
                                    @endif
                                </td>

                                <td class="text-right text-sm opacity-70">
                                    {{ $report->updated_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center opacity-70 py-8">No reports yet.</td>
                            </tr>
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

