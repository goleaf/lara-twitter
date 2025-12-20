<div>
    <button
        type="button"
        wire:click="openModal"
        class="{{ $this->buttonClass }}"
        wire:loading.attr="disabled"
        wire:target="openModal"
        @disabled(!auth()->check())
    >
        {{ $this->label }}
    </button>

    @if ($this->showNotice && $this->submittedCaseNumber)
        <x-callout type="success" class="mt-2" role="status">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    Report submitted (case <span class="font-mono">{{ $this->submittedCaseNumber }}</span>)
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
        </x-callout>
    @endif

    <dialog class="modal" @if($this->open) open @endif wire:keydown.escape.prevent="closeModal">
        <div class="modal-box card bg-base-100 p-0">
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-lg">Report</h3>
                        <p class="text-sm opacity-70 mt-1">Flag content for review.</p>
                    </div>
                    <button
                        type="button"
                        class="btn btn-ghost btn-sm btn-square"
                        wire:click="closeModal"
                        wire:loading.attr="disabled"
                        wire:target="closeModal,submit"
                        aria-label="Close"
                    >
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="submit" class="mt-4 space-y-3">
                    <div>
                        <label class="label">
                            <span class="label-text">Reason</span>
                        </label>
                        <select wire:model="reason" class="select select-bordered select-sm w-full" wire:loading.attr="disabled" wire:target="submit">
                            <option value="">Select a reason</option>
                            @foreach ($reasonOptions as $group => $options)
                                <optgroup label="{{ $group }}" wire:key="report-reason-group-{{ md5($group) }}">
                                    @foreach ($options as $value => $label)
                                        <option value="{{ $value }}" wire:key="report-reason-{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                    </div>

                    <div>
                        @php($detailsRequired = in_array($this->reason, \App\Models\Report::reasonsRequiringDetails(), true))
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
        </div>

        <form method="dialog" class="modal-backdrop">
            <button type="button" wire:click="closeModal" wire:loading.attr="disabled" wire:target="closeModal,submit">
                <span class="sr-only">Close</span>
            </button>
        </form>
    </dialog>
</div>
