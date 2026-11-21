// Gallery: category views (hash-driven) + lightbox.
// Without JS every section is simply shown one after another.
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('gallery');
    if (!root) return;

    const panels = [...root.querySelectorAll('[data-panel]')];
    const chips = [...root.querySelectorAll('[data-chip]')];
    const valid = new Set(panels.map((p) => p.dataset.panel));

    root.classList.add('is-enhanced');

    const show = (key, scroll = true) => {
        if (!valid.has(key)) key = 'all';

        panels.forEach((p) => { p.hidden = p.dataset.panel !== key; });
        chips.forEach((c) => {
            const on = c.dataset.chip === key;
            c.classList.toggle('is-on', on);
            on ? c.setAttribute('aria-current', 'page') : c.removeAttribute('aria-current');
        });

        if (scroll) window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const fromHash = () => (location.hash || '#all').slice(1);

    window.addEventListener('hashchange', () => show(fromHash()));
    show(fromHash(), false);

    // ---------- Lightbox ----------
    const box = document.getElementById('galLightbox');
    const big = box.querySelector('img');
    let group = [];
    let index = 0;

    const render = () => {
        const { full, alt } = group[index].dataset;
        big.src = full;
        big.alt = alt || '';
    };

    const open = (button) => {
        group = [...button.closest('.gal-section').querySelectorAll('.gal-open')];
        index = group.indexOf(button);
        render();
        box.hidden = false;
        document.body.classList.add('gal-lock');
        box.querySelector('.gal-lb-close').focus();
    };

    const close = () => {
        box.hidden = true;
        document.body.classList.remove('gal-lock');
    };

    const step = (d) => {
        index = (index + d + group.length) % group.length;
        render();
    };

    root.querySelectorAll('.gal-open').forEach((b) => b.addEventListener('click', () => open(b)));
    box.querySelector('.gal-lb-close').addEventListener('click', close);
    box.querySelector('.gal-lb-prev').addEventListener('click', () => step(-1));
    box.querySelector('.gal-lb-next').addEventListener('click', () => step(1));
    box.addEventListener('click', (e) => { if (e.target === box) close(); });

    // Swipe left or right on a touch screen (the arrows are hidden there)
    let touchX = null;
    let touchY = null;

    box.addEventListener('touchstart', (e) => {
        touchX = e.changedTouches[0].clientX;
        touchY = e.changedTouches[0].clientY;
    }, { passive: true });

    box.addEventListener('touchend', (e) => {
        if (touchX === null) return;
        const dx = e.changedTouches[0].clientX - touchX;
        const dy = e.changedTouches[0].clientY - touchY;
        touchX = null;
        if (Math.abs(dx) > 48 && Math.abs(dx) > Math.abs(dy)) step(dx < 0 ? 1 : -1);
    }, { passive: true });

    document.addEventListener('keydown', (e) => {
        if (box.hidden) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') step(-1);
        if (e.key === 'ArrowRight') step(1);
    });
});
