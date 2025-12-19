@php($maxBodyLength = auth()->user()?->is_premium ? 25000 : 280)
@php($bodyLength = mb_strlen($body))
@php($me = auth()->user())
@php($avatarLabel = $me?->name ?? $me?->username ?? 'You')
@php($avatarInitial = mb_strtoupper(mb_substr($avatarLabel, 0, 1)))

@php($pollHasInput = count(array_filter($poll_options, static fn (mixed $v): bool => is_string($v) && trim($v) !== '')) > 0)
@php($pollOpen = $pollHasInput || $poll_duration || $errors->has('poll_options') || $errors->has('poll_options.*') || $errors->has('poll_duration'))

<div class="card bg-base-100 border">
    <div class="card-body gap-4">
        <div class="flex items-center justify-between gap-4">
            <div class="font-semibold">Post</div>
            <div class="text-xs {{ $bodyLength > $maxBodyLength ? 'text-error' : 'opacity-70' }}">
                {{ $bodyLength }}/{{ $maxBodyLength }}
            </div>
        </div>

        <div class="flex items-start gap-3">
            <a class="shrink-0 pt-1" href="{{ route('profile.show', ['user' => $me?->username]) }}" wire:navigate aria-label="View profile">
                <div class="avatar">
                    <div class="w-10 rounded-full border border-base-200 bg-base-100">
                        @if ($me?->avatar_url)
                            <img src="{{ $me->avatar_url }}" alt="" />
                        @else
                            <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                {{ $avatarInitial }}
                            </div>
                        @endif
                    </div>
                </div>
            </a>

            <div class="min-w-0 flex-1 space-y-4">
                <div>
                    <textarea
                        wire:model.live="body"
                        class="textarea textarea-bordered textarea-sm w-full"
                        rows="3"
                        placeholder="What’s happening?"
                    ></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </div>

                <div class="flex items-center justify-between gap-3">
                    <label class="text-sm opacity-70">Who can reply?</label>
                    <select wire:model="reply_policy" class="select select-bordered select-sm max-w-xs w-full">
                        <option value="everyone">Everyone</option>
                        <option value="following">Only people you follow</option>
                        <option value="mentioned">Only people you mention</option>
                        <option value="none">No one</option>
                    </select>
                </div>
                <x-input-error :messages="$errors->get('reply_policy')" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-sm opacity-70">Location (optional)</label>
                        <input wire:model="location" type="text" class="input input-bordered input-sm w-full" placeholder="e.g. Prague" />
                        <x-input-error :messages="$errors->get('location')" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm opacity-70">Schedule (optional)</label>
                        <input wire:model="scheduled_for" type="datetime-local" class="input input-bordered input-sm w-full" />
                        <x-input-error :messages="$errors->get('scheduled_for')" />
                    </div>
                </div>
                <div class="text-xs opacity-70">Scheduled posts won’t appear until they’re published.</div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <input wire:model="images" type="file" multiple accept="image/*" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                        <input wire:model="video" type="file" accept="video/mp4,video/webm,video/*" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    </div>
                    <button wire:click="save" class="btn btn-primary btn-sm" @disabled(trim($body) === '' || $bodyLength > $maxBodyLength)>Post</button>
                </div>
                <div class="text-xs opacity-70">Up to 4 images or 1 video.</div>
                <x-input-error :messages="$errors->get('images')" />
                <x-input-error :messages="$errors->get('images.*')" />
                <x-input-error :messages="$errors->get('video')" />

                <details class="collapse collapse-arrow bg-base-200/50 border border-base-200" @if ($pollOpen) open @endif>
                    <summary class="collapse-title font-semibold">Poll (optional)</summary>
                    <div class="collapse-content space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input wire:model="poll_options.0" type="text" class="input input-bordered input-sm w-full" placeholder="Option 1" />
                            <input wire:model="poll_options.1" type="text" class="input input-bordered input-sm w-full" placeholder="Option 2" />
                            <input wire:model="poll_options.2" type="text" class="input input-bordered input-sm w-full" placeholder="Option 3" />
                            <input wire:model="poll_options.3" type="text" class="input input-bordered input-sm w-full" placeholder="Option 4" />
                        </div>
                        <x-input-error :messages="$errors->get('poll_options')" />
                        <x-input-error :messages="$errors->get('poll_options.*')" />

                        <div class="flex items-center justify-between gap-3">
                            <label class="text-sm opacity-70">Duration</label>
                            <select wire:model="poll_duration" class="select select-bordered select-sm max-w-xs w-full">
                                <option value="">Select...</option>
                                <option value="1440">1 day</option>
                                <option value="4320">3 days</option>
                                <option value="10080">7 days</option>
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('poll_duration')" />

                        <div class="text-xs opacity-70">Polls can’t be combined with images or video.</div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>
