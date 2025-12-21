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
                <div class="text-xs tabular-nums {{ $isTooLong ? 'text-error' : 'opacity-70' }}">
                    {{ $bodyLength }}/{{ $maxLength }}
                </div>
            </div>

            <progress
                class="progress progress-primary h-1 w-full"
                value="{{ min($bodyLength, $maxLength) }}"
                max="{{ $maxLength }}"
                aria-hidden="true"
            ></progress>

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

            <div class="rounded-box border border-base-200 bg-base-200/40 p-3 space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        wire:model="media"
                        type="file"
                        multiple
                        accept="image/*,video/mp4,video/webm,video/*"
                        class="file-input file-input-bordered file-input-sm w-full max-w-xs"
                        wire:loading.attr="disabled"
                        wire:target="media"
                    />
                    @if (count($media))
                        <span class="text-xs opacity-70">{{ count($media) }} selected</span>
                    @endif
                </div>

                @php($mediaCount = count($media))
                @php($hasVideo = collect($media)->contains(fn ($file) => str_starts_with((string) ($file->getMimeType() ?? ''), 'video/')))
                @php($hasImage = collect($media)->contains(fn ($file) => str_starts_with((string) ($file->getMimeType() ?? ''), 'image/')))
                @php($mediaIsVideo = $hasVideo && ! $hasImage)
                @if ($mediaCount)
                    @php($gridClass = $mediaIsVideo || $mediaCount === 1 ? 'grid-cols-1' : 'grid-cols-2')

                    <div class="grid {{ $gridClass }} gap-2">
                        @foreach ($media as $index => $file)
                            @php($mime = (string) ($file->getMimeType() ?? ''))
                            @php($isVideo = str_starts_with($mime, 'video/'))
                            @php($ratio = $mediaIsVideo || $mediaCount === 1 ? '16 / 9' : '1 / 1')

                            <div class="relative overflow-hidden rounded-box border border-base-200 bg-base-200" style="aspect-ratio: {{ $ratio }};" wire:key="reply-media-{{ $file->getFilename() }}">
                                @if (method_exists($file, 'isPreviewable') && $file->isPreviewable())
                                    @if ($isVideo)
                                        <video class="h-full w-full" controls preload="metadata">
                                            <source src="{{ $file->temporaryUrl() }}" type="{{ $mime }}" />
                                        </video>
                                    @else
                                        <img class="h-full w-full object-cover" src="{{ $file->temporaryUrl() }}" alt="" loading="lazy" decoding="async" />
                                    @endif
                                @else
                                    <div class="h-full w-full flex items-center justify-center text-xs opacity-70">
                                        {{ $file->getClientOriginalName() }}
                                    </div>
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-ghost btn-xs absolute top-2 right-2 bg-base-100/90"
                                    wire:click="removeMedia({{ $index }})"
                                    wire:loading.attr="disabled"
                                    wire:target="removeMedia({{ $index }})"
                                    aria-label="Remove media"
                                >
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="text-xs opacity-70">Up to 4 images or 1 video.</div>
            </div>

            <x-input-error class="mt-2" :messages="$errors->get('media')" />

            <div class="flex items-center justify-end gap-2">
                <button
                    class="btn btn-primary btn-sm"
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save,media,removeMedia"
                    @disabled(trim($body) === '' || $isTooLong)
                >
                    Reply
                </button>
            </div>
        </div>
    </div>
</form>
