// Menu book: the "Explore food menu" button opens a modal with a closed book (cover + "Click here"
// hint). Clicking the cover opens it like a real book; pages turn with the arrows, a click on a page,
// the keyboard or a swipe. On phones the cover opens and the pages are then read by scrolling.

const phone = window.matchMedia('(max-width: 820px)');

class MenuBook {
    constructor(root) {
        this.root = root;
        this.book = root.querySelector('[data-mb-book]');
        this.sheets = [...root.querySelectorAll('[data-mb-sheet]')];
        this.turns = Number(root.dataset.turns || 0);
        this.pages = Number(root.dataset.pages || 0);
        this.scroll = root.querySelector('[data-mb-scroll]');
        this.count = root.querySelector('[data-mb-count]');
        this.flipped = 0;
        this.opening = false;
        this.closing = false;
        this.opener = null;
        this.timers = [];
        this.coverReadyTimer = null;
        this.lockUntil = 0;
        this.cascading = null;

        // The X button and the dark area around the book both close the whole menu, from any page.
        this.root.querySelectorAll('[data-mb-close], .mb-close, .mb-backdrop').forEach((el) => {
            el.addEventListener('click', (event) => { event.preventDefault(); this.close(); });
        });
        this.root.querySelector('[data-mb-open]')?.addEventListener('click', () => this.openCover());
        this.root.querySelectorAll('[data-mb-next]').forEach((el) => el.addEventListener('click', () => this.go(1)));
        this.root.querySelectorAll('[data-mb-prev]').forEach((el) => el.addEventListener('click', () => this.go(-1)));

        this.jumps = [...root.querySelectorAll('[data-mb-jump]')];
        this.jumps.forEach((el) => el.addEventListener('click', () => this.jump(Number(el.dataset.mbJump))));
        this.scroll?.addEventListener('scroll', () => requestAnimationFrame(() => this.markJump()), { passive: true });
        window.addEventListener('resize', () => { window.clearTimeout(this.fitTimer); this.fitTimer = window.setTimeout(() => this.fit(), 150); });

        this.keyHandler = (event) => this.onKey(event);
        this.initSwipe();
        this.initRatio();
        this.render(0);
    }

    // Page shape comes from the cover picture, so any portrait menu size fits.
    initRatio() {
        const cover = this.sheets[0]?.querySelector('img');
        const apply = () => {
            if (cover && cover.naturalWidth && cover.naturalHeight) {
                this.root.style.setProperty('--mb-r', (cover.naturalWidth / cover.naturalHeight).toFixed(4));
            }
        };
        if (cover?.complete) apply(); else cover?.addEventListener('load', apply, { once: true });
    }

    open(opener) {
        this.opener = opener || null;
        // Load every page picture now, so no photo pops in while a page is turning.
        this.root.querySelectorAll('img[loading="lazy"]').forEach((img) => { img.loading = 'eager'; img.decode?.().catch(() => {}); });
        this.reset();
        this.root.hidden = false;
        // Text that is too long for its page is scaled down to fit once the book is laid out.
        requestAnimationFrame(() => { this.fit(); window.setTimeout(() => this.fit(), 500); });
        document.documentElement.classList.add('mb-lock');
        document.addEventListener('keydown', this.keyHandler);
        // Next frame, so the fade and zoom-in play.
        requestAnimationFrame(() => requestAnimationFrame(() => this.root.classList.add('is-visible')));
        // Restore the invitation only after the closed cover is mounted and visible.
        this.root.classList.add('is-cover-ready');
        this.root.dataset.mbCoverButton = 'visible';
        this.root.querySelector('[data-mb-open]')?.focus({ preventScroll: true });
    }

    // Return to the front cover without dismissing the menu dialog.
    returnToCover() {
        if (this.closing || this.root.hidden) return;
        window.clearTimeout(this.coverReadyTimer);
        this.timers.forEach(window.clearTimeout);
        this.timers = [];
        this.clearCascade();
        this.opening = false;
        this.root.classList.remove('is-reading', 'is-lifting', 'is-closing', 'is-cover-ready');
        if (this.scroll) this.scroll.hidden = true;
        const previous = this.flipped;
        this.flipped = 0;
        // Set every page behind the front cover; never dismiss the modal.
        this.render(previous);
        const durations = getComputedStyle(this.sheets[0]).transitionDuration.split(',');
        const ms = Math.max(0, ...durations.map(v => v.trim().endsWith('ms') ? parseFloat(v) : parseFloat(v) * 1000));
        window.clearTimeout(this.coverReadyTimer);
        this.coverReadyTimer = window.setTimeout(() => {
            if (this.flipped !== 0 || this.closing || this.root.hidden) return;
            this.root.classList.add('mb-instant-reset');
            this.sheets.forEach((sheet, i) => {
                sheet.classList.remove('is-flipped', 'is-lift', 'turn-fwd', 'turn-back', 'mb-paper-curl');
                sheet.style.zIndex = this.sheets.length - i;
            });
            this.book.classList.add('is-closed');
            void this.book.offsetWidth;
            this.root.classList.remove('mb-instant-reset');
            this.root.classList.add('is-cover-ready');
        this.root.dataset.mbCoverButton = 'visible';
            this.coverReadyTimer = null;
        }, ms + 80);
    }

    // Closing: an open book is first folded shut (its pages turn back one after another and the cover closes),
    // then the dialog fades away. A book that is already closed, a phone (pages are scrolled) or a visitor who
    // prefers reduced motion get just the fade. It is put back to the closed cover only after the dialog is
    // fully hidden, so the next opening always starts from the cover with its "Open Menu" button.
    close() {
        if (this.root.hidden) return;
        if (this.closing && performance.now() - (this.closeStarted || 0) < 3000) return;
        this.closing = true;
        this.closeStarted = performance.now();
        this.opening = false;
        window.clearTimeout(this.coverReadyTimer);
        this.coverReadyTimer = null;
        this.timers.forEach(window.clearTimeout);
        this.timers = [];
        this.clearCascade();
        document.removeEventListener('keydown', this.keyHandler);
        this.root.dataset.mbCoverButton = 'hidden';
        this.root.classList.remove('is-cover-ready', 'is-lifting');
        // Controls and section tabs go away right away; the book stays on screen while it closes.
        this.root.classList.add('is-closing');

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const fold = !reduced && !phone.matches && this.flipped > 0 && !this.root.classList.contains('is-reading');
        let wait = 0;
        if (fold) {
            this.book.style.transitionDuration = '.6s'; // the closed book slides to the middle a little quicker
            wait = this.cascade(0, { turn: 0.6, gap: 0.1, force: true }) + 220;
        }

        this.timers.push(window.setTimeout(() => {
            // Fades the backdrop and lets the stage sink away (see the .mb and .mb-stage transitions).
            this.root.classList.remove('is-visible');
        }, wait));
        this.timers.push(window.setTimeout(() => {
            this.root.hidden = true;
            document.documentElement.classList.remove('mb-lock');
            this.reset(); // back to the closed cover while nothing is on screen
            this.opener?.focus?.({ preventScroll: true });
        }, wait + (reduced ? 0 : 420)));
    }

    reset() {
        this.lockUntil = 0;
        window.clearTimeout(this.coverReadyTimer);
        this.coverReadyTimer = null;
        this.timers.forEach(window.clearTimeout);
        this.timers = [];
        this.clearCascade();
        this.root.classList.remove('is-open', 'is-reading', 'is-lifting', 'is-closing', 'is-cover-ready', 'mb-instant-reset');
        this.closing = false;
        this.opening = false;
        this.book.classList.remove('is-opening', 'is-end');
        this.book.style.transitionDuration = '';
        if (this.scroll) { this.scroll.hidden = true; this.scroll.scrollTop = 0; }

        // Reset all sheets without calling render(previousPage), which would
        // trigger an unwanted reverse page-turn after the dialog closes.
        this.root.classList.add('mb-instant-reset');
        this.flipped = 0;
        this.sheets.forEach((sheet, i) => {
            sheet.classList.remove('is-flipped', 'is-lift', 'turn-fwd', 'turn-back', 'mb-paper-curl');
            sheet.style.zIndex = this.sheets.length - i;
        });
        this.book.classList.add('is-closed');
        this.root.classList.remove('is-end');
        this.root.querySelectorAll('[data-mb-prev]').forEach((el) => { el.disabled = true; });
        this.root.querySelectorAll('[data-mb-next]').forEach((el) => { el.disabled = this.turns === 0; });
        if (this.count) this.count.textContent = '';
        this.markJump();
        // Force the final closed geometry before restoring transitions.
        void this.book.offsetWidth;
        this.root.classList.remove('mb-instant-reset');
        this.root.classList.add('is-cover-ready');
        this.root.dataset.mbCoverButton = 'visible';
    }

    // The cover: opens the book (or, on a phone, swings the cover open and then shows the pages to scroll).
    openCover() {
        if (this.opening || this.closing) return;
        window.clearTimeout(this.coverReadyTimer);
        this.coverReadyTimer = null;
        this.opening = true;
        this.root.classList.remove('is-cover-ready');
        this.root.dataset.mbCoverButton = 'hidden';
        const cover = this.sheets[0];

        // The cover lifts a little first, then swings open.
        this.root.classList.add('is-lifting');
        cover.classList.add('is-lift');

        this.timers.push(window.setTimeout(() => {
            cover.classList.remove('is-lift');
            this.root.classList.remove('is-lifting');
            this.opening = false;

            if (phone.matches) {
                this.book.classList.add('is-opening');
                this.flipped = 1;
                this.render(0);
                this.timers.push(window.setTimeout(() => {
                    this.root.classList.add('is-reading');
                    if (this.scroll) { this.scroll.hidden = false; this.scroll.scrollTop = 0; }
                    this.markJump();
                    this.root.querySelector('.mb-close')?.focus({ preventScroll: true });
                }, 1300));
                return;
            }

            this.go(1);
            this.root.querySelector('[data-mb-next]:not([tabindex="-1"])')?.focus({ preventScroll: true });
        }, 300));
    }

    // A dish page never lets its text run into the legend: if the dishes need more room than the page has,
    // the text on that page is made a little smaller until it fits (never below 60%).
    fit() {
        if (this.root.hidden) return;
        this.root.querySelectorAll('.mb-stage .mbp').forEach((page) => {
            const box = page.querySelector('.mbp-dishes');
            const texts = [...page.querySelectorAll('.mbp-text')];
            if (!box || !texts.length) return;
            texts.forEach((text) => text.style.removeProperty('--u'));
            let scale = 1;
            while (box.scrollHeight > box.clientHeight + 1 && scale > 0.6) {
                scale -= 0.04;
                texts.forEach((text) => text.style.setProperty('--u', `calc(var(--pw) / 100 * ${scale.toFixed(2)})`));
            }
        });
    }

    // Jump to the page where a category starts. On a phone the list scrolls there; in the book, a spread
    // next to the current one turns normally, and a farther one is reached by turning every page in between.
    jump(face) {
        if (this.closing || this.opening || !face) return;

        if (this.root.classList.contains('is-reading')) {
            const page = this.scroll?.querySelectorAll('.mb-scroll-page')[face - 1];
            if (page) this.scroll.scrollTo({ top: page.offsetTop - 64, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
            return;
        }

        const target = Math.min(this.turns, Math.max(1, Math.ceil(face / 2)));
        if (target === this.flipped) return;
        if (Math.abs(target - this.flipped) === 1) { this.go(target - this.flipped); return; }

        this.cascade(target);
    }

    // Jump to a far-away spread by turning every page in between, one after another (a quick "thumb through"),
    // instead of fading. Pages turn faster than a normal single turn and overlap, so even the last category
    // is reached in about a second and a half.
    cascade(target, { turn: turnSeconds = 0.72, gap: maxGap = 0.14, force = false } = {}) {
        const now = performance.now();
        if (!force && now < this.lockUntil) return 0;

        const from = this.flipped;
        const forward = target > from;
        const count = Math.abs(target - from);
        const total = this.sheets.length;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const turn = reduced ? 0.01 : turnSeconds;                                   // seconds for one page
        const gap = reduced || count < 2 ? 0 : Math.min(maxGap, 0.85 / (count - 1)); // seconds between page starts
        const spanMs = Math.round((turn + gap * (count - 1)) * 1000);
        this.lockUntil = now + spanMs + 60;

        window.clearTimeout(this.coverReadyTimer);
        this.coverReadyTimer = null;
        this.root.classList.remove('is-cover-ready');
        this.root.dataset.mbCoverButton = 'hidden';

        // Sheets in the order they turn: forwards from the current one, backwards from the last turned one.
        const order = [];
        if (forward) for (let i = from; i < target; i++) order.push(i);
        else for (let i = from - 1; i >= target; i--) order.push(i);

        this.flipped = target;
        this.cascading = order;

        order.forEach((index, step) => {
            const sheet = this.sheets[index];
            const delay = step * gap;
            const direction = forward ? 'turn-fwd' : 'turn-back';

            sheet.style.setProperty('--mb-turn', `${turn}s`);
            sheet.style.transitionDuration = `${turn}s`;
            sheet.style.transitionDelay = `${delay}s`;
            sheet.classList.remove('turn-fwd', 'turn-back', 'mb-paper-curl');
            sheet.classList.toggle('is-flipped', forward);

            // A page only moves up to the "in the air" layer at the moment it starts turning (never earlier,
            // or a page that is still waiting would cover the one on top for a split second). Pages that start
            // later sit above the ones that left before them. The paper shading starts together with the page.
            const lift = () => {
                sheet.style.zIndex = 200 + step;
                sheet.classList.remove('turn-fwd', 'turn-back');
                void sheet.offsetWidth;
                sheet.classList.add(direction);
            };
            if (delay === 0) lift();
            else this.timers.push(window.setTimeout(lift, delay * 1000));
            // Once past the middle the page settles into its resting layer.
            this.timers.push(window.setTimeout(() => {
                sheet.style.zIndex = forward ? index + 1 : total - index;
            }, (delay + turn / 2) * 1000));
        });

        // Everything has landed: remove the temporary timing so ordinary page turns look as before.
        this.timers.push(window.setTimeout(() => this.clearCascade(), spanMs + 80));

        // Folding shut: the book only moves to the middle once the cover itself starts to close.
        if (target === 0) this.timers.push(window.setTimeout(() => this.syncUI(), (count - 1) * gap * 1000));
        else this.syncUI();

        return spanMs;
    }

    clearCascade() {
        if (!this.cascading) return;
        this.cascading = null;
        this.sheets.forEach((sheet) => {
            sheet.style.removeProperty('--mb-turn');
            sheet.style.transitionDuration = '';
            sheet.style.transitionDelay = '';
            sheet.classList.remove('turn-fwd', 'turn-back', 'mb-paper-curl');
        });
        this.sheets.forEach((sheet, i) => {
            sheet.style.zIndex = i < this.flipped ? i + 1 : this.sheets.length - i;
        });
    }

    // Highlights the section of the page(s) on screen.
    markJump() {
        if (!this.jumps.length) return;
        let current = null;

        if (this.root.classList.contains('is-reading') && this.scroll) {
            const pages = [...this.scroll.querySelectorAll('.mb-scroll-page')];
            const line = this.scroll.scrollTop + 110;
            pages.forEach((page, i) => { if (page.offsetTop <= line) current = i + 1; });
        } else if (this.flipped > 0) {
            current = Math.min(this.flipped * 2, this.pages);
        }

        let active = null;
        this.jumps.forEach((el) => { if (current !== null && Number(el.dataset.mbJump) <= current) active = el; });
        this.jumps.forEach((el) => {
            const on = el === active;
            el.classList.toggle('is-active', on);
            if (on) {
                el.setAttribute('aria-current', 'true');
                const strip = el.parentElement;
                if (strip && strip.scrollWidth > strip.clientWidth) strip.scrollTo({ left: el.offsetLeft - (strip.clientWidth - el.clientWidth) / 2, behavior: 'smooth' });
            } else {
                el.removeAttribute('aria-current');
            }
        });
    }

    go(step) {
        if (this.closing) return;
        const target = Math.min(this.turns, Math.max(0, this.flipped + step));
        if (target === this.flipped) return;
        // One page turn at a time: a click during a turn is ignored instead of making the pages jump.
        const now = performance.now();
        if (now < this.lockUntil) return;
        this.lockUntil = now + 520;
        window.clearTimeout(this.coverReadyTimer);
        this.coverReadyTimer = null;
        this.root.classList.remove('is-cover-ready');
        this.root.dataset.mbCoverButton = 'hidden';
        const before = this.flipped;
        if (target === 0) {
            this.returnToCover();
            return;
        }
        this.flipped = target;
        this.render(before);
    }

    render(before) {
        const total = this.sheets.length;

        this.sheets.forEach((sheet, i) => {
            const isFlipped = i < this.flipped;
            const wasFlipped = i < before;
            const settled = isFlipped ? i + 1 : total - i;

            sheet.classList.toggle('is-flipped', isFlipped);

            if (isFlipped !== wasFlipped) {
                // The sheet that is turning stays on top until it has passed the middle, and its two
                // sides are shaded while it turns.
                sheet.style.zIndex = 200;
                const turn = parseFloat(getComputedStyle(sheet).transitionDuration) * 1000 || 1150;
                const direction = isFlipped ? 'turn-fwd' : 'turn-back';
                sheet.classList.remove('turn-fwd', 'turn-back', 'mb-paper-curl');
                void sheet.offsetWidth; // restart the shading animation
                sheet.classList.add(direction);
                this.timers.push(window.setTimeout(() => { sheet.style.zIndex = settled; }, turn / 2));
                this.timers.push(window.setTimeout(() => { sheet.classList.remove(direction, 'mb-paper-curl'); }, turn + 80));
            } else {
                sheet.style.zIndex = settled;
            }
        });

        this.syncUI();
    }

    // Book state -> arrows, page count, section tabs and cover invitation (shared by every kind of page turn).
    syncUI() {
        const total = this.sheets.length;
        const closed = this.flipped === 0;
        const atEnd = this.flipped === total;

        this.book.classList.toggle('is-closed', closed);
        this.book.classList.toggle('is-end', atEnd);
        this.root.classList.toggle('is-open', !closed);
        this.root.classList.toggle('is-end', atEnd);

        // Returning with the previous arrow (or a swipe) is not a modal reset.
        // Wait until the front cover finishes its rotation before restoring
        // the invitation, and cancel it if another turn starts meanwhile.
        if (closed && !this.closing && !this.opening && !this.root.hidden) {
            const cover = this.sheets[0];
            const duration = cover ? getComputedStyle(cover).transitionDuration
                .split(',').map((v) => v.trim().endsWith('ms') ? parseFloat(v) : parseFloat(v) * 1000)
                .reduce((a, b) => Math.max(a, b), 0) : 0;
            this.coverReadyTimer = window.setTimeout(() => {
                if (this.flipped === 0 && !this.closing && !this.opening && !this.root.hidden) {
                    this.root.classList.add('is-cover-ready');
        this.root.dataset.mbCoverButton = 'visible';
                }
                this.coverReadyTimer = null;
            }, Number.isFinite(duration) ? duration + 60 : 1200);
        }

        this.markJump();

        const canPrev = this.flipped > 0;
        const canNext = this.flipped < this.turns;
        this.root.querySelectorAll('[data-mb-prev]').forEach((el) => { el.disabled = !canPrev; });
        this.root.querySelectorAll('[data-mb-next]').forEach((el) => { el.disabled = !canNext; });

        if (this.count) {
            if (closed || this.pages === 0) {
                this.count.textContent = '';
            } else {
                const left = this.flipped * 2 - 1;
                const right = this.flipped * 2;
                this.count.textContent = right > this.pages || atEnd
                    ? `Page ${left} of ${this.pages}`
                    : `Pages ${left}–${right} of ${this.pages}`;
            }
        }
    }

    onKey(event) {
        if (event.key === 'Escape') { event.preventDefault(); this.close(); return; }
        if (event.key === 'Tab') { this.trapFocus(event); return; }
        if (this.root.classList.contains('is-reading')) return;
        if (event.key === 'ArrowRight') { event.preventDefault(); if (this.flipped > 0) this.go(1); }
        if (event.key === 'ArrowLeft') { event.preventDefault(); this.go(-1); }
    }

    // Keep Tab / Shift+Tab inside the open menu (it is a modal dialog).
    trapFocus(event) {
        const items = [...this.root.querySelectorAll('button:not([disabled]):not([tabindex="-1"])')]
            .filter((el) => el.offsetParent !== null && getComputedStyle(el).visibility !== 'hidden');
        if (!items.length) { event.preventDefault(); return; }
        const first = items[0];
        const last = items[items.length - 1];
        if (!this.root.contains(document.activeElement)) { event.preventDefault(); first.focus(); }
        else if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }

    initSwipe() {
        const stage = this.root.querySelector('[data-mb-stage]');
        let startX = null;
        let startY = null;

        stage.addEventListener('pointerdown', (e) => { startX = e.clientX; startY = e.clientY; });
        stage.addEventListener('pointerup', (e) => {
            if (startX === null) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            startX = startY = null;
            if (!this.closing && Math.abs(dx) > 55 && Math.abs(dx) > Math.abs(dy) * 1.5 && this.flipped > 0) this.go(dx < 0 ? 1 : -1);
        });
        stage.addEventListener('pointercancel', () => { startX = startY = null; });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const books = new Map();

    document.querySelectorAll('[data-mb]').forEach((root) => books.set(root.id, new MenuBook(root)));

    document.querySelectorAll('[data-menu-book]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            const book = books.get(trigger.dataset.menuBook);
            if (!book) return;
            event.preventDefault();
            book.open(trigger);
        });
    });
});
