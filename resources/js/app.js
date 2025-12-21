import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

function setupNavigateProgress() {
    const bar = document.getElementById('navigate-progress-bar');
    if (!bar) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

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

        if (prefersReducedMotion.matches) {
            bar.style.transitionDuration = '0ms';
            bar.style.width = '100%';
            bar.style.opacity = '1';
            return;
        }

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

        if (prefersReducedMotion.matches) {
            bar.style.transitionDuration = '0ms';
            bar.style.opacity = '0';
            bar.style.width = '0%';
            return;
        }

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
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const behavior = prefersReducedMotion ? 'auto' : 'smooth';
        target.scrollIntoView({ behavior, block: 'start' });
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

function setupLivewireUploadState() {
    if (window.livewireUploadStateReady) {
        return;
    }

    window.livewireUploadStateReady = true;

    const root = document.documentElement;
    let uploadCount = 0;

    const update = () => {
        root.classList.toggle('livewire-uploading', uploadCount > 0);
    };

    const increment = () => {
        uploadCount += 1;
        update();
    };

    const decrement = () => {
        uploadCount = Math.max(0, uploadCount - 1);
        update();
    };

    document.addEventListener('livewire-upload-start', increment);
    document.addEventListener('livewire-upload-finish', decrement);
    document.addEventListener('livewire-upload-error', decrement);
    document.addEventListener('livewire-upload-cancel', decrement);
}

function setupSidebarToggles() {
    document.querySelectorAll('[data-open-sidebar]').forEach((button) => {
        if (button.dataset.sidebarToggleBound === '1') {
            return;
        }

        button.dataset.sidebarToggleBound = '1';

        button.addEventListener('click', () => {
            window.dispatchEvent(new Event('open-sidebar'));
        });
    });
}

document.addEventListener('DOMContentLoaded', scrollToHash);
window.addEventListener('hashchange', scrollToHash);
document.addEventListener('livewire:navigated', scrollToHash);
document.addEventListener('DOMContentLoaded', setupNavigateProgress);
document.addEventListener('DOMContentLoaded', setupLivewireNavigateSearchForms);
document.addEventListener('livewire:navigated', setupLivewireNavigateSearchForms);
document.addEventListener('DOMContentLoaded', setupLivewireUploadState);
document.addEventListener('DOMContentLoaded', setupSidebarToggles);
document.addEventListener('livewire:navigated', setupSidebarToggles);

function setupEcho() {
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
    if (!pusherKey) {
        return;
    }

    const cluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;
    const echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: cluster || undefined,
        forceTLS: true,
    });

    window.Echo = echo;

    const userId = window.AppConfig?.userId;
    if (userId) {
        echo.channel(`timeline.${userId}`).listen('.post.created', (event) => {
            if (window.Livewire?.dispatch) {
                window.Livewire.dispatch('new-post-available', { post: event.post });
            }
        });
    }

    if (userId) {
        echo.join('online')
            .here((users) => {
                window.dispatchEvent(new CustomEvent('presence-online', { detail: { users } }));
            })
            .joining((user) => {
                window.dispatchEvent(new CustomEvent('presence-joining', { detail: { user } }));
            })
            .leaving((user) => {
                window.dispatchEvent(new CustomEvent('presence-leaving', { detail: { user } }));
            });
    }
}

document.addEventListener('DOMContentLoaded', setupEcho);
