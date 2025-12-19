<div class="card bg-base-100 border">
    <div class="card-body gap-3">
        <div class="font-semibold">Post</div>

        <textarea
            wire:model.live="body"
            class="textarea textarea-bordered w-full"
            rows="3"
            placeholder="What’s happening?"
        ></textarea>
        <x-input-error :messages="$errors->get('body')" />

        <div class="flex items-center justify-between gap-3">
            <label class="text-sm opacity-70">Who can reply?</label>
            <select wire:model="reply_policy" class="select select-bordered max-w-xs w-full">
                <option value="everyone">Everyone</option>
                <option value="following">Only people you follow</option>
                <option value="mentioned">Only people you mention</option>
                <option value="none">No one</option>
            </select>
        </div>
        <x-input-error :messages="$errors->get('reply_policy')" />

        <div class="flex items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <input wire:model="images" type="file" multiple accept="image/*" class="file-input file-input-bordered w-full max-w-xs" />
                <input wire:model="video" type="file" accept="video/mp4,video/webm,video/*" class="file-input file-input-bordered w-full max-w-xs" />
            </div>
            <button wire:click="save" class="btn btn-primary">Post</button>
        </div>
        <div class="text-xs opacity-70">Up to 4 images or 1 video.</div>
        <x-input-error :messages="$errors->get('images')" />
        <x-input-error :messages="$errors->get('images.*')" />
        <x-input-error :messages="$errors->get('video')" />
    </div>
</div>
