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

        <div class="divider my-2"></div>

        <div class="space-y-2">
            <div class="font-semibold">Poll (optional)</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <input wire:model="poll_options.0" type="text" class="input input-bordered w-full" placeholder="Option 1" />
                <input wire:model="poll_options.1" type="text" class="input input-bordered w-full" placeholder="Option 2" />
                <input wire:model="poll_options.2" type="text" class="input input-bordered w-full" placeholder="Option 3" />
                <input wire:model="poll_options.3" type="text" class="input input-bordered w-full" placeholder="Option 4" />
            </div>
            <x-input-error :messages="$errors->get('poll_options')" />
            <x-input-error :messages="$errors->get('poll_options.*')" />

            <div class="flex items-center justify-between gap-3">
                <label class="text-sm opacity-70">Duration</label>
                <select wire:model="poll_duration" class="select select-bordered max-w-xs w-full">
                    <option value="">Select...</option>
                    <option value="1440">1 day</option>
                    <option value="4320">3 days</option>
                    <option value="10080">7 days</option>
                </select>
            </div>
            <x-input-error :messages="$errors->get('poll_duration')" />

            <div class="text-xs opacity-70">Polls can’t be combined with images or video.</div>
        </div>
    </div>
</div>
