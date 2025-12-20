<div
    x-data="{
        observe() {
            if (!this.$el.dataset.hasMore) {
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        $wire.dispatch('load-more');
                    }
                });
            }, {
                rootMargin: '100px'
            });

            observer.observe(this.$el);
        }
    }"
    x-init="observe()"
    data-has-more="{{ $hasMore ? '1' : '' }}"
    class="flex justify-center py-8"
>
    <div wire:loading>
        <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>

    @if (! $hasMore)
        <div class="text-center text-base-content/60">
            You've reached the end!
        </div>
    @endif
</div>
