import './bootstrap';

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

document.addEventListener('DOMContentLoaded', scrollToHash);
window.addEventListener('hashchange', scrollToHash);
document.addEventListener('livewire:navigated', scrollToHash);
