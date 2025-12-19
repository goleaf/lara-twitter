@php($me = auth()->user())
@php($profileHref = $me ? route('profile.show', ['user' => $me->username]) : null)
@php($avatarLabel = $me?->name ?? $me?->username ?? 'You')
@php($avatarInitial = mb_strtoupper(mb_substr($avatarLabel, 0, 1)))
@php($bodyLength = mb_strlen($body))
@php($isTooLong = $bodyLength > $maxLength)

<form wire:submit="save" class="space-y-3">
    <div class="flex items-start gap-3">
        <div class="shrink-0 pt-1">
            @if ($profileHref)
                <a href="{{ $profileHref }}" wire:navigate aria-label="View profile">
            @endif

            <div class="avatar">
                    <div class="w-10 rounded-full border border-base-200 bg-base-100">
                        @if ($me?->avatar_url)
                            <img src="{{ $me->avatar_url }}" alt="" loading="lazy" decoding="async" />
                        @else
                            <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                {{ $avatarInitial }}
                            </div>
                        @endif
                </div>
            </div>

            @if ($profileHref)
                </a>
            @endif
        </div>

        <div class="min-w-0 flex-1 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <div class="text-xs opacity-70">Reply</div>
                <div class="text-xs {{ $isTooLong ? 'text-error' : 'opacity-70' }}">
                    {{ $bodyLength }}/{{ $maxLength }}
                </div>
            </div>

            <div>
                <textarea
                    wire:model="body"
                    class="textarea textarea-bordered textarea-sm w-full"
                    rows="3"
                    placeholder="Write a reply..."
                    maxlength="{{ $maxLength }}"
                ></textarea>
                <x-input-error class="mt-2" :messages="$errors->get('body')" />
            </div>

            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                    <input wire:model="images" type="file" multiple accept="image/*" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <input wire:model="video" type="file" accept="video/mp4,video/webm,video/*" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                </div>

                <div class="flex items-center justify-between gap-2">
                    <div class="text-xs opacity-70">Up to 4 images or 1 video.</div>
                    @if ($video)
                        <button type="button" wire:click="removeVideo" class="btn btn-ghost btn-xs">Remove video</button>
                    @endif
                </div>

                <x-input-error class="mt-2" :messages="$errors->get('images')" />
                <x-input-error class="mt-2" :messages="$errors->get('images.*')" />
                <x-input-error class="mt-2" :messages="$errors->get('video')" />
            </div>

            <div class="flex items-center justify-end gap-2">
                <button class="btn btn-primary btn-sm" type="submit" @disabled(trim($body) === '' || $isTooLong)>Reply</button>
            </div>
        </div>
    </div>
</form>
