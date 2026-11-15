// Winterresidenz page: full-screen photo viewer for the photos of the open tab.
// Arrows / swipe / keyboard to browse, click the photo (or Zoom) to zoom, thumbnails below.
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-wr]');
    if (!root) return;

    const viewer = document.createElement('div');
    viewer.className = 'wr-viewer';
    viewer.hidden = true;
    viewer.setAttribute('role', 'dialog');
    viewer.setAttribute('aria-modal', 'true');
    viewer.setAttribute('aria-label', 'Fotos');
    viewer.innerHTML = `
        <div class="wr-viewer__bar">
            <span class="wr-viewer__count" aria-live="polite"></span>
            <button type="button" data-wr-zoom-toggle aria-label="Foto vergrößern">Zoom +</button>
            <button type="button" data-wr-close aria-label="Schließen" style="font-size:1.6rem;line-height:1">×</button>
        </div>
        <div class="wr-viewer__stage">
            <button type="button" class="wr-viewer__nav wr-viewer__nav--prev" data-wr-prev aria-label="Vorheriges Foto">‹</button>
            <img class="wr-viewer__img" alt="">
            <button type="button" class="wr-viewer__nav wr-viewer__nav--next" data-wr-next aria-label="Nächstes Foto">›</button>
        </div>
        <p class="wr-viewer__caption"></p>
        <div class="wr-viewer__thumbs"></div>`;
    document.body.appendChild(viewer);

    const img = viewer.querySelector('.wr-viewer__img');
    const count = viewer.querySelector('.wr-viewer__count');
    const caption = viewer.querySelector('.wr-viewer__caption');
    const thumbs = viewer.querySelector('.wr-viewer__thumbs');
    const zoomButton = viewer.querySelector('[data-wr-zoom-toggle]');
    const navButtons = viewer.querySelectorAll('[data-wr-prev], [data-wr-next]');

    let items = [];
    let index = 0;
    let zoomed = false;
    let lastFocus = null;
    let touchX = null;

    const setZoom = (on) => {
        zoomed = on;
        img.classList.toggle('is-zoomed', on);
        zoomButton.textContent = on ? 'Zoom −' : 'Zoom +';
    };

    const show = (next, instant = false) => {
        index = (next + items.length) % items.length;
        const item = items[index];
        setZoom(false);

        const apply = () => {
            img.src = item.full;
            img.alt = item.alt;
            img.classList.remove('is-switching');
        };

        if (instant) {
            apply();
        } else {
            img.classList.add('is-switching');
            setTimeout(apply, 140);
        }

        count.textContent = `${index + 1} / ${items.length}`;
        caption.textContent = item.caption;
        caption.hidden = item.caption === '';
        thumbs.querySelectorAll('button').forEach((thumb, i) => {
            thumb.setAttribute('aria-current', i === index ? 'true' : 'false');
            if (i === index) thumb.scrollIntoView({ block: 'nearest', inline: 'center' });
        });

        // Preload the neighbours so browsing feels instant.
        [index - 1, index + 1].forEach((n) => {
            const neighbour = items[(n + items.length) % items.length];
            if (neighbour) new Image().src = neighbour.full;
        });
    };

    const open = (trigger) => {
        const panel = trigger.closest('[data-wr-panel]') || root;
        items = [...panel.querySelectorAll('[data-wr-zoom]')].map((button) => ({
            button,
            full: button.dataset.full,
            caption: (button.dataset.caption || '').trim(),
            alt: button.querySelector('img')?.alt || '',
        }));
        if (!items.length) return;

        thumbs.innerHTML = '';
        items.forEach((item, i) => {
            const thumb = document.createElement('button');
            thumb.type = 'button';
            thumb.setAttribute('aria-label', `Foto ${i + 1}`);
            thumb.innerHTML = `<img src="${item.full}" alt="" loading="lazy">`;
            thumb.addEventListener('click', () => show(i));
            thumbs.appendChild(thumb);
        });
        thumbs.hidden = items.length < 2;
        navButtons.forEach((button) => { button.hidden = items.length < 2; });

        lastFocus = document.activeElement;
        viewer.hidden = false;
        document.body.classList.add('wr-viewer-open');
        show(items.findIndex((item) => item.button === trigger), true);
        viewer.querySelector('[data-wr-close]').focus();
    };

    const close = () => {
        viewer.hidden = true;
        document.body.classList.remove('wr-viewer-open');
        img.removeAttribute('src');
        lastFocus?.focus?.();
    };

    root.querySelectorAll('[data-wr-zoom]').forEach((button) => button.addEventListener('click', () => open(button)));

    viewer.querySelector('[data-wr-close]').addEventListener('click', close);
    viewer.querySelector('[data-wr-prev]').addEventListener('click', () => show(index - 1));
    viewer.querySelector('[data-wr-next]').addEventListener('click', () => show(index + 1));
    zoomButton.addEventListener('click', () => setZoom(!zoomed));
    img.addEventListener('click', () => setZoom(!zoomed));
    viewer.addEventListener('click', (event) => {
        if (event.target === viewer || event.target.classList.contains('wr-viewer__stage')) close();
    });

    document.addEventListener('keydown', (event) => {
        if (viewer.hidden) return;
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowLeft') show(index - 1);
        if (event.key === 'ArrowRight') show(index + 1);
        if (event.key === 'Tab') {
            const controls = [...viewer.querySelectorAll('button')].filter((button) => !button.hidden && button.offsetParent !== null);
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });

    viewer.addEventListener('touchstart', (event) => { touchX = event.changedTouches[0]?.clientX ?? null; }, { passive: true });
    viewer.addEventListener('touchend', (event) => {
        if (touchX === null || zoomed) { touchX = null; return; }
        const dx = (event.changedTouches[0]?.clientX ?? touchX) - touchX;
        if (Math.abs(dx) > 55) show(index + (dx < 0 ? 1 : -1));
        touchX = null;
    }, { passive: true });
});
