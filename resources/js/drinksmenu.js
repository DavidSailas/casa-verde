// Drinks menu: the EXPLORE DRINKS MENU button opens a full-screen menu with category tabs,
// a scroll-aware active tab, a filter and a back-to-top link.

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dm]').forEach(initDrinksMenu);

    document.querySelectorAll('[data-drinks-menu]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const dialog = document.getElementById(trigger.dataset.drinksMenu);
            if (!dialog || dialog.open) return;
            dialog.showModal();
            dialog.dispatchEvent(new CustomEvent('dm:open'));
        });
    });
});

function initDrinksMenu(dialog) {
    const scroller = dialog.querySelector('[data-dm-scroll]');
    const filterBar = dialog.querySelector('[data-dm-filters]');
    const tabs = [...dialog.querySelectorAll('[data-dm-tab]')];
    const tabStrip = dialog.querySelector('[data-dm-tabs]');
    const sections = [...dialog.querySelectorAll('[data-dm-section]')];
    const items = [...dialog.querySelectorAll('.dm-item')];
    const chips = [...dialog.querySelectorAll('[data-dm-filter]')];
    const empty = dialog.querySelector('[data-dm-empty]');
    const found = dialog.querySelector('[data-dm-found]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let lockSpy = false;
    let lockTimer = null;
    let stickyHeight = 0;

    // The header spans the full width but the scrolling area gives up room to its scrollbar:
    // hand that width to the header so both stay centred on the same line. Sections also stop
    // below the sticky filter row (when it is sticky) when a tab is used.
    const measure = () => {
        const scrollbar = Math.max(0, scroller.offsetWidth - scroller.clientWidth);
        const sticky = filterBar && getComputedStyle(filterBar).position === 'sticky';

        stickyHeight = sticky ? filterBar.offsetHeight : 0;
        dialog.style.setProperty('--dm-sbw', `${scrollbar}px`);
        dialog.style.setProperty('--dm-sticky', `${stickyHeight}px`);
    };
    const observer = new ResizeObserver(measure);
    observer.observe(scroller);
    if (filterBar) observer.observe(filterBar);

    const visibleSections = () => sections.filter((section) => !section.hidden);

    const setActive = (slug) => {
        tabs.forEach((tab) => {
            const on = tab.dataset.dmTab === slug;
            tab.classList.toggle('is-active', on);
            if (on) tab.setAttribute('aria-current', 'true'); else tab.removeAttribute('aria-current');
            if (on && tabStrip) {
                const left = tab.offsetLeft - (tabStrip.clientWidth - tab.clientWidth) / 2;
                tabStrip.scrollTo({ left, behavior: reduceMotion ? 'auto' : 'smooth' });
            }
        });
    };

    tabs.forEach((tab) => tab.addEventListener('click', (event) => {
        event.preventDefault();
        const section = sections.find((s) => s.dataset.dmSection === tab.dataset.dmTab);
        if (!section) return;
        lockSpy = true;
        clearTimeout(lockTimer);
        lockTimer = setTimeout(() => { lockSpy = false; }, 800);
        setActive(tab.dataset.dmTab);
        section.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    }));

    // The tab follows whichever category is under the sticky rows while scrolling. At the very
    // bottom the last category wins, even when it is too short to reach the top of the screen.
    const spy = () => {
        if (lockSpy) return;

        const shown = visibleSections();
        if (shown.length === 0) return;

        const atBottom = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 2;
        const line = scroller.getBoundingClientRect().top + stickyHeight + 48;
        let current = shown[0];

        shown.forEach((section) => {
            if (section.getBoundingClientRect().top <= line) current = section;
        });

        if (atBottom && scroller.scrollTop > 0) current = shown[shown.length - 1];

        setActive(current.dataset.dmSection);
    };
    scroller.addEventListener('scroll', () => requestAnimationFrame(spy), { passive: true });

    const applyFilter = (tag) => {
        let visible = 0;

        items.forEach((item) => {
            const show = !tag || item.dataset.tags.split(' ').includes(tag);
            item.hidden = !show;
            if (show) visible += 1;
        });

        sections.forEach((section) => {
            section.hidden = ![...section.querySelectorAll('.dm-item')].some((item) => !item.hidden);
        });

        tabs.forEach((tab) => {
            const section = sections.find((s) => s.dataset.dmSection === tab.dataset.dmTab);
            tab.hidden = !!section?.hidden;
        });

        chips.filter((chip) => chip.classList.contains('dm-chip')).forEach((chip) => {
            const on = (chip.dataset.dmFilter || '') === (tag || '');
            chip.classList.toggle('is-on', on);
            chip.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        if (empty) empty.hidden = visible !== 0;
        if (found) found.textContent = tag ? `${visible} ${visible === 1 ? 'drink' : 'drinks'}` : '';

        scroller.scrollTo({ top: 0, behavior: 'auto' });
        spy();
    };

    chips.forEach((chip) => chip.addEventListener('click', () => applyFilter(chip.dataset.dmFilter || '')));

    dialog.querySelector('[data-dm-close]')?.addEventListener('click', () => dialog.close());

    dialog.querySelector('[data-dm-top]')?.addEventListener('click', () => {
        scroller.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });

    // Always opens at the top, with everything showing. Focus goes to the scrolling area so the
    // arrow keys, Space and Page Down work straight away; Escape closes the menu.
    dialog.addEventListener('dm:open', () => {
        measure();
        applyFilter('');
        setActive(sections[0]?.dataset.dmSection);
        scroller.focus({ preventScroll: true });
    });
}
