// Villas page: click a villa photo to open its photo gallery (a full-screen viewer with thumbnails and booking buttons).

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-vm]').forEach(initGallery);

    document.querySelectorAll('[data-vm-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.vmOpen);
            if (!dialog || dialog.open) return;
            dialog.showModal();
            dialog.dispatchEvent(new CustomEvent('vm:open'));
        });
    });
});

function initGallery(dialog) {
    const slides = [...dialog.querySelectorAll('.vm-slide')];
    const thumbs = [...dialog.querySelectorAll('[data-vm-go]')];
    const strip = dialog.querySelector('.vm-thumbs');
    const counter = dialog.querySelector('[data-vm-count]');
    const stage = dialog.querySelector('.vm-stage');
    const count = slides.length;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let active = 0;

    // Photos next to the current one are fetched early so a click never shows an empty frame.
    const preload = (index) => {
        const img = slides[(index + count) % count]?.querySelector('img');
        if (img) img.loading = 'eager';
    };

    const render = () => {
        slides.forEach((slide, index) => {
            const on = index === active;
            slide.classList.toggle('is-active', on);
            slide.setAttribute('aria-hidden', on ? 'false' : 'true');
        });

        thumbs.forEach((thumb, index) => {
            if (index === active) thumb.setAttribute('aria-current', 'true');
            else thumb.removeAttribute('aria-current');
        });

        if (counter) counter.textContent = `${active + 1} / ${count}`;

        // Keep the chosen thumbnail in the middle of the strip.
        const current = thumbs[active];
        if (strip && current) {
            const left = current.offsetLeft - (strip.clientWidth - current.clientWidth) / 2;
            strip.scrollTo({ left, behavior: reduceMotion || !dialog.open ? 'auto' : 'smooth' });
        }

        preload(active + 1);
        preload(active - 1);
    };

    const go = (index) => {
        active = (index + count) % count;
        render();
    };

    dialog.querySelector('[data-vm-prev]')?.addEventListener('click', () => go(active - 1));
    dialog.querySelector('[data-vm-next]')?.addEventListener('click', () => go(active + 1));
    dialog.querySelector('[data-vm-close]')?.addEventListener('click', () => dialog.close());
    thumbs.forEach((thumb, index) => thumb.addEventListener('click', () => go(index)));

    // Clicking the dark area around the photo closes the gallery.
    dialog.addEventListener('click', (event) => {
        const t = event.target;
        if (t === dialog || t.classList.contains('vm-shell') || t === stage || t.classList.contains('vm-frame')) {
            dialog.close();
        }
    });

    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') { go(active - 1); event.preventDefault(); }
        if (event.key === 'ArrowRight') { go(active + 1); event.preventDefault(); }
        if (event.key === 'Home') { go(0); event.preventDefault(); }
        if (event.key === 'End') { go(count - 1); event.preventDefault(); }
    });

    // Swipe on touch screens.
    let startX = null;

    stage.addEventListener('pointerdown', (event) => { startX = event.clientX; });
    stage.addEventListener('pointerup', (event) => {
        if (startX === null) return;
        const dx = event.clientX - startX;
        startX = null;
        if (Math.abs(dx) > 40) go(active + (dx < 0 ? 1 : -1));
    });
    stage.addEventListener('pointercancel', () => { startX = null; });

    // Always opens on the first photo.
    dialog.addEventListener('close', () => go(0));
    dialog.addEventListener('vm:open', render);

    render();
}
