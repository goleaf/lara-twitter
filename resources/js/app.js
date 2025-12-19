import './bootstrap';

function setupNavigateProgress() {
    const bar = document.getElementById('navigate-progress-bar');
    if (!bar) {
        return;
    }

    let progressTimer;
    let finishTimer;

    const clearTimers = () => {
        if (progressTimer) {
            window.clearTimeout(progressTimer);
            progressTimer = undefined;
        }
        if (finishTimer) {
            window.clearTimeout(finishTimer);
            finishTimer = undefined;
        }
    };

    const start = () => {
        clearTimers();

        bar.style.transitionDuration = '0ms';
        bar.style.width = '0%';
        bar.style.opacity = '1';

        bar.getBoundingClientRect();

        bar.style.transitionDuration = '300ms';
        bar.style.width = '35%';

        progressTimer = window.setTimeout(() => {
            bar.style.width = '85%';
        }, 250);
    };

    const finish = () => {
        clearTimers();

        bar.style.transitionDuration = '250ms';
        bar.style.width = '100%';

        finishTimer = window.setTimeout(() => {
            bar.style.opacity = '0';
            bar.style.width = '0%';
        }, 200);
    };

    document.addEventListener('livewire:navigating', start);
    document.addEventListener('livewire:navigated', finish);
}

function scrollToHash() {
    const { hash } = window.location;
    if (!hash || hash === '#') {
        return;
    }

    let id = hash.slice(1);
    try {
        id = decodeURIComponent(id);
    } catch {
        // noop
    }

    const target = document.getElementById(id);
    if (!target) {
        return;
    }

    requestAnimationFrame(() => {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

function setupLivewireNavigateSearchForms() {
    document.querySelectorAll('form[data-livewire-navigate-search]').forEach((form) => {
        if (form.dataset.livewireNavigateSearchBound === '1') {
            return;
        }

        form.dataset.livewireNavigateSearchBound = '1';

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const action = form.getAttribute('action') || window.location.pathname;
            const formData = new FormData(form);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (typeof value !== 'string') {
                    continue;
                }

                const trimmedValue = value.trim();
                if (!trimmedValue) {
                    continue;
                }

                params.set(key, trimmedValue);
            }

            const url = params.toString() ? `${action}?${params.toString()}` : action;

            const navigate = window.Livewire?.navigate;
            if (typeof navigate === 'function') {
                navigate(url);
                return;
            }

            window.location.assign(url);
        });
    });
}

document.addEventListener('DOMContentLoaded', scrollToHash);
window.addEventListener('hashchange', scrollToHash);
document.addEventListener('livewire:navigated', scrollToHash);
document.addEventListener('DOMContentLoaded', setupNavigateProgress);
document.addEventListener('DOMContentLoaded', setupLivewireNavigateSearchForms);
document.addEventListener('livewire:navigated', setupLivewireNavigateSearchForms);
