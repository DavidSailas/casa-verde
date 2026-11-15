// Yoga Reset booking form: shows only the fields that fit the chosen experience, keeps the
// "Your request" summary up to date, handles the participants stepper and the quick length-of-stay
// buttons, ticks off the three steps as they are completed, and drives the mobile summary bar.
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-ybk-form]');
    if (!form) return;

    const radios = [...form.querySelectorAll('input[name="type"]')];
    const start = form.querySelector('[data-ybk-start]');
    const end = form.querySelector('[data-ybk-end]');
    const hint = form.querySelector('[data-ybk-hint]');
    const session = form.querySelector('[data-ybk-session]');
    const people = form.querySelector('input[name="participants"]');
    const startLabel = form.querySelector('[data-ybk-start-label]');
    const submit = form.querySelector('[data-ybk-submit]');
    const sections = [...form.querySelectorAll('[data-ybk-section]')];
    const steps = [...document.querySelectorAll('[data-ybk-step]')];
    const bar = document.querySelector('[data-ybk-bar]');
    const barText = document.querySelector('[data-sum-bar]');
    const nightButtons = [...form.querySelectorAll('[data-nights]')];
    const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const sum = (key) => form.querySelector(`[data-sum="${key}"]`);
    const row = (key) => form.querySelector(`[data-sum-row="${key}"]`);
    const day = (value) => new Date(`${value}T00:00:00`);
    // Local date as YYYY-MM-DD. toISOString() works in UTC, which is a day off for guests east of Greenwich.
    const iso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const fmt = (value) => {
        if (!value) return '';
        const d = day(value);
        return Number.isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
    };
    const type = () => (radios.find((r) => r.checked) || {}).value || '';
    const priceText = () => document.querySelector('.ybk .bk-price strong')?.textContent.trim();
    const filled = (name) => !!form.querySelector(`[name="${name}"]`)?.value.trim();

    const update = () => {
        const t = type();
        const chosen = radios.find((r) => r.checked);

        form.querySelectorAll('[data-for]').forEach((el) => el.classList.toggle('is-on', el.dataset.for === t));
        if (end) end.required = t === 'retreat';
        if (session) session.required = t === 'class';

        const label = t === 'retreat' ? 'Arrival' : 'Preferred date';
        if (startLabel) startLabel.textContent = label;
        sum('start-label').textContent = label;
        sum('title').textContent = chosen ? chosen.dataset.title : '—';

        row('session').hidden = t !== 'class';
        sum('session').textContent = session && session.value ? session.options[session.selectedIndex].text : 'Choose a class';
        sum('start').textContent = fmt(start.value) || 'Choose a date';

        // Departure can never be before the day after arrival
        if (start.value && end) {
            const next = day(start.value);
            next.setDate(next.getDate() + 1);
            end.min = iso(next);
        }

        let nights = 0;
        if (t === 'retreat' && start.value && end && end.value) {
            nights = Math.round((day(end.value) - day(start.value)) / 86400000);
        }
        row('end').hidden = !(t === 'retreat' && nights > 0);
        row('nights').hidden = !(t === 'retreat' && nights > 0);
        if (nights > 0) {
            sum('end').textContent = fmt(end.value);
            sum('nights').textContent = `${nights} ${nights === 1 ? 'night' : 'nights'}`;
        }
        if (hint) {
            hint.classList.toggle('is-on', t === 'retreat');
            hint.textContent = nights > 0
                ? `${nights} ${nights === 1 ? 'night' : 'nights'} · ${fmt(start.value)} to ${fmt(end.value)}`
                : (end && end.value && start.value ? 'Departure must be after arrival.' : 'Select your dates to see how many nights.');
        }
        nightButtons.forEach((b) => b.classList.toggle('is-active', nights > 0 && Number(b.dataset.nights) === nights));

        const guests = Number(people.value) || 1;
        sum('people').textContent = guests;
        sum('price').textContent = t === 'retreat' && priceText() ? priceText() : 'Confirmed by our team';
        if (barText) barText.textContent = `${chosen ? chosen.dataset.title : 'Yoga Reset'} · ${guests} ${guests === 1 ? 'guest' : 'guests'}`;

        // Tick off the steps
        const done = [
            !!t && (t !== 'class' || !session || !!session.value),
            !!start.value && (t !== 'retreat' || nights > 0) && guests >= 1,
            ['name', 'email', 'phone', 'country'].every(filled),
        ];
        sections.forEach((el, i) => el.classList.toggle('is-done', !!done[i]));
        steps.forEach((el, i) => el.classList.toggle('is-done', !!done[i]));
    };

    form.addEventListener('input', update);
    form.addEventListener('change', update);

    // Participants stepper
    form.querySelectorAll('[data-ybk-stepper]').forEach((box) => {
        const input = box.querySelector('input');
        const min = Number(box.dataset.min);
        const max = Number(box.dataset.max);
        const sync = () => {
            const v = Math.min(max, Math.max(min, Number(input.value) || min));
            input.value = v;
            box.querySelector('[data-step="-1"]').disabled = v <= min;
            box.querySelector('[data-step="1"]').disabled = v >= max;
            update();
        };
        box.addEventListener('click', (event) => {
            const button = event.target.closest('[data-step]');
            if (!button) return;
            input.value = (Number(input.value) || min) + Number(button.dataset.step);
            sync();
        });
        input.addEventListener('change', sync);
        sync();
    });

    // Quick length of stay: sets the departure date from the arrival date
    nightButtons.forEach((button) => button.addEventListener('click', () => {
        if (!start.value) {
            start.classList.add('is-invalid');
            start.focus();
            if (hint) hint.textContent = 'Choose your arrival date first, then pick the length of your stay.';
            return;
        }
        const d = day(start.value);
        d.setDate(d.getDate() + Number(button.dataset.nights));
        end.value = iso(d);
        end.classList.remove('is-invalid');
        update();
    }));

    // Step tracker: highlight the step that is on screen
    if (steps.length) steps[0].classList.add('is-active');
    if ('IntersectionObserver' in window && sections.length) {
        const spy = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const n = sections.indexOf(entry.target);
                steps.forEach((el, i) => el.classList.toggle('is-active', i === n));
            });
        }, { rootMargin: '-35% 0px -55% 0px' });
        sections.forEach((el) => spy.observe(el));
    }

    // Phone/tablet bar: shown while the form is on screen and the send button is not
    if (bar && submit && 'IntersectionObserver' in window) {
        let inForm = false;
        let atSubmit = false;
        const toggle = () => {
            const show = inForm && !atSubmit;
            bar.classList.toggle('is-shown', show);
            bar.setAttribute('aria-hidden', show ? 'false' : 'true');
            bar.querySelector('button').tabIndex = show ? 0 : -1;
        };
        const cols = form.querySelector('.bk-cols');
        new IntersectionObserver(([e]) => { inForm = e.isIntersecting; toggle(); }).observe(cols);
        new IntersectionObserver(([e]) => { atSubmit = e.isIntersecting; toggle(); }, { rootMargin: '0px 0px -60px 0px' }).observe(submit);
        bar.querySelector('[data-ybk-jump]').addEventListener('click', () => {
            submit.scrollIntoView({ behavior: calm ? 'auto' : 'smooth', block: 'center' });
        });
    }

    // A busy button stops double submits; the first invalid field is shown if the browser skipped it
    form.addEventListener('submit', (event) => {
        const missing = [...form.querySelectorAll('input[required], select[required]')].find((el) => {
            if (el.offsetParent === null) return false;
            return el.type === 'checkbox' ? !el.checked : (el.type === 'radio' ? false : !el.value);
        });
        if (missing) {
            event.preventDefault();
            missing.classList.add('is-invalid');
            missing.scrollIntoView({ behavior: calm ? 'auto' : 'smooth', block: 'center' });
            missing.focus({ preventScroll: true });
            return;
        }
        submit.disabled = true;
        submit.classList.add('is-busy');
    });

    form.addEventListener('input', (event) => event.target.classList?.remove('is-invalid'));
    form.addEventListener('change', (event) => event.target.classList?.remove('is-invalid'));

    update();

    const alert = form.querySelector('[data-bk-alert]');
    if (alert) alert.focus();
});
