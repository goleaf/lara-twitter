<form wire:submit="save" class="space-y-3">
	    <textarea
	        wire:model="body"
	        class="textarea textarea-bordered textarea-sm w-full"
	        rows="3"
	        placeholder="Write a reply..."
	        maxlength="{{ $maxLength }}"
	    ></textarea>
	    <x-input-error class="mt-2" :messages="$errors->get('body')" />

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
        <button class="btn btn-primary btn-sm" type="submit">Reply</button>
    </div>
</form>
