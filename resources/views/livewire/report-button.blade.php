<div>
    <button type="button" wire:click="openModal" class="btn btn-ghost btn-xs" @disabled(!auth()->check())>
        {{ $label }}
    </button>

    <dialog class="modal" @if($open) open @endif>
        <div class="modal-box">
            <h3 class="font-semibold text-lg">Report</h3>
            <p class="text-sm opacity-70 mt-1">Flag content for review.</p>

            <form wire:submit.prevent="submit" class="mt-4 space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text">Reason</span>
                    </label>
                    <select wire:model="reason" class="select select-bordered w-full">
                        <option value="">Select a reason</option>
                        @foreach ($reasonOptions as $group => $options)
                            <optgroup label="{{ $group }}">
                                @foreach ($options as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                </div>

                <div>
                    @php($detailsRequired = in_array($reason, \App\Models\Report::reasonsRequiringDetails(), true))
                    <label class="label">
                        <span class="label-text">Details {{ $detailsRequired ? '(required)' : '(optional)' }}</span>
                    </label>
                    <textarea
                        wire:model="details"
                        class="textarea textarea-bordered w-full"
                        rows="3"
                        placeholder="Add context for the reviewer..."
                    ></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('details')" />
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" class="btn btn-ghost btn-sm">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                </div>
            </form>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button type="button" wire:click="closeModal">close</button>
        </form>
    </dialog>
</div>
