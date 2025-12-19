<div>
    <button
        type="button"
        wire:click="openModal"
        class="{{ $buttonClass }}"
        wire:loading.attr="disabled"
        wire:target="openModal"
        @disabled(!auth()->check())
    >
        {{ $label }}
    </button>

    @if ($showNotice && $submittedCaseNumber)
        <div class="mt-2 alert alert-success py-2" role="status">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm4.03 7.47a.75.75 0 0 1 0 1.06l-4.95 4.95a.75.75 0 0 1-1.06 0l-2.05-2.05a.75.75 0 1 1 1.06-1.06l1.52 1.52 4.42-4.42a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
            <div class="min-w-0">
                <div class="text-sm">
                    Report submitted (case <span class="font-mono">{{ $submittedCaseNumber }}</span>)
                </div>
            </div>
            <div class="flex items-center gap-1">
                <a class="btn btn-ghost btn-xs" href="{{ route('reports.index') }}" wire:navigate>View</a>
                <button
                    type="button"
                    class="btn btn-ghost btn-xs"
                    wire:click="clearNotice"
                    wire:loading.attr="disabled"
                    wire:target="clearNotice"
                >
                    Dismiss
                </button>
            </div>
        </div>
    @endif

    <dialog class="modal" @if($open) open @endif>
        <div class="modal-box">
            <h3 class="font-semibold text-lg">Report</h3>
            <p class="text-sm opacity-70 mt-1">Flag content for review.</p>

            <form wire:submit.prevent="submit" class="mt-4 space-y-3">
                <div>
                    <label class="label">
                        <span class="label-text">Reason</span>
                    </label>
                    <select wire:model="reason" class="select select-bordered select-sm w-full" wire:loading.attr="disabled" wire:target="submit">
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
                        class="textarea textarea-bordered textarea-sm w-full"
                        rows="3"
                        placeholder="Add context for the reviewer..."
                        wire:loading.attr="disabled"
                        wire:target="submit"
                    ></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('details')" />
                </div>

                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="btn btn-ghost btn-sm"
                        wire:loading.attr="disabled"
                        wire:target="closeModal,submit"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary btn-sm"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button type="button" wire:click="closeModal" wire:loading.attr="disabled" wire:target="closeModal,submit">close</button>
        </form>
    </dialog>
</div>
