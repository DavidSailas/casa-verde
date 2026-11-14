// Food menu: the EXPLORE FOOD MENU button opens a full-screen menu with category tabs,
// a scroll-aware active tab and an ingredient filter.

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-fm]').forEach(initMenu);

    document.querySelectorAll('[data-food-menu]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const dialog = document.getElementById(trigger.dataset.foodMenu);
            if (!dialog || dialog.open) return;
            dialog.showModal();
            dialog.dispatchEvent(new CustomEvent('fm:open'));
        });
    });
});

function initMenu(dialog) {
    const scroller = dialog.querySelector('[data-fm-scroll]');
    const bar = dialog.querySelector('[data-fm-bar]');
    const tabs = [...dialog.querySelectorAll('[data-fm-tab]')];
    const tabStrip = dialog.querySelector('[data-fm-tabs]');
    const sections = [...dialog.querySelectorAll('[data-fm-section]')];
    const dishes = [...dialog.querySelectorAll('.fm-dish')];
    const chips = [...dialog.querySelectorAll('[data-fm-filter]')];
    const empty = dialog.querySelector('[data-fm-empty]');
    const found = dialog.querySelector('[data-fm-found]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let lockSpy = false;
    let lockTimer = null;

    // Sections stop just below the sticky bar when a tab is used.
    const measure = () => dialog.style.setProperty('--fm-bar', `${bar.offsetHeight}px`);
    new ResizeObserver(measure).observe(bar);

    const setActive = (slug) => {
        tabs.forEach((tab) => {
            const on = tab.dataset.fmTab === slug;
            tab.classList.toggle('is-active', on);
            if (on) tab.setAttribute('aria-current', 'true'); else tab.removeAttribute('aria-current');
            if (on && tabStrip) {
                const left = tab.offsetLeft - (tabStrip.clientWidth - tab.clientWidth) / 2;
                tabStrip.scrollTo({ left, behavior: reduceMotion ? 'auto' : 'smooth' });
            }
        });
    };

    // Tabs scroll to their category.
    tabs.forEach((tab) => tab.addEventListener('click', (event) => {
        event.preventDefault();
        const section = sections.find((s) => s.dataset.fmSection === tab.dataset.fmTab);
        if (!section) return;
        lockSpy = true;
        clearTimeout(lockTimer);
        lockTimer = setTimeout(() => { lockSpy = false; }, 800);
        setActive(tab.dataset.fmTab);
        section.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    }));

    // The tab follows whichever category is under the bar while scrolling.
    const spy = () => {
        if (lockSpy) return;
        const line = scroller.getBoundingClientRect().top + bar.offsetHeight + 40;
        let current = null;
        sections.forEach((section) => {
            if (!section.hidden && section.getBoundingClientRect().top <= line) current = section;
        });
        if (current) setActive(current.dataset.fmSection);
    };
    scroller.addEventListener('scroll', () => requestAnimationFrame(spy), { passive: true });

    // Ingredient filter.
    const applyFilter = (tag) => {
        let visible = 0;

        dishes.forEach((dish) => {
            const show = !tag || dish.dataset.tags.split(' ').includes(tag);
            dish.hidden = !show;
            if (show) visible += 1;
        });

        sections.forEach((section) => {
            const any = [...section.querySelectorAll('.fm-dish')].some((dish) => !dish.hidden);
            section.hidden = !any;
        });

        tabs.forEach((tab) => {
            const section = sections.find((s) => s.dataset.fmSection === tab.dataset.fmTab);
            tab.hidden = !!section?.hidden;
        });

        chips.filter((chip) => chip.classList.contains('fm-chip')).forEach((chip) => {
            const on = (chip.dataset.fmFilter || '') === (tag || '');
            chip.classList.toggle('is-on', on);
            chip.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        if (empty) empty.hidden = visible !== 0;
        if (found) found.textContent = tag ? `${visible} ${visible === 1 ? 'dish' : 'dishes'}` : '';

        scroller.scrollTo({ top: 0, behavior: 'auto' });
        spy();
    };

    chips.forEach((chip) => chip.addEventListener('click', () => applyFilter(chip.dataset.fmFilter || '')));

    dialog.querySelector('[data-fm-close]')?.addEventListener('click', () => dialog.close());

    // Always opens at the top, with everything showing.
    dialog.addEventListener('fm:open', () => {
        applyFilter('');
        measure();
        scroller.scrollTo({ top: 0, behavior: 'auto' });
        setActive(sections[0]?.dataset.fmSection);
        dialog.querySelector('[data-fm-close]')?.focus({ preventScroll: true });
    });
}
