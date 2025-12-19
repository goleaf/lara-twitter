@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success text-sm py-2']) }} role="status">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm4.03 7.47a.75.75 0 0 1 0 1.06l-4.95 4.95a.75.75 0 0 1-1.06 0l-2.05-2.05a.75.75 0 1 1 1.06-1.06l1.52 1.52 4.42-4.42a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
        </svg>
        <span class="min-w-0">{{ $status }}</span>
    </div>
@endif
