<form wire:submit="save" class="space-y-3">
    <textarea
        wire:model="body"
        class="textarea textarea-bordered w-full"
        rows="3"
        placeholder="Write a reply..."
        maxlength="{{ $maxLength }}"
    ></textarea>
    @error('body') <div class="text-error text-sm">{{ $message }}</div> @enderror

    <input wire:model="images" type="file" multiple class="file-input file-input-bordered w-full" />
    @error('images') <div class="text-error text-sm">{{ $message }}</div> @enderror
    @error('images.*') <div class="text-error text-sm">{{ $message }}</div> @enderror

    <div class="flex items-center justify-end gap-2">
        <button class="btn btn-primary btn-sm" type="submit">Reply</button>
    </div>
</form>
