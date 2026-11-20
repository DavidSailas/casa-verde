{{-- Pictures as WebP: new uploads are converted automatically; this converts the ones uploaded before. --}}
<style>
.webp-card { margin-top: 32px; padding: 24px 26px; border: 1px solid #dcd0bc; border-radius: 20px; background: #fff; box-shadow: 0 18px 36px -30px rgba(6, 33, 27, .45); }
.webp-card__top { display: flex; align-items: flex-start; gap: 18px; }
.webp-card__icon { flex: none; display: grid; place-items: center; width: 48px; height: 48px; border-radius: 14px; background: #06211b; color: #dfc294; }
.webp-card__intro { min-width: 0; flex: 1; }
.webp-card__titlerow { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 12px; }
.webp-card__title { margin: 0; font-size: 1.2rem; font-weight: 600; line-height: 1.25; color: #06211b; }
.webp-card__text { margin: 8px 0 0; max-width: 46rem; font-size: .95rem; line-height: 1.6; color: #52625c; }
.webp-badge { display: inline-flex; align-items: center; gap: 7px; padding: 4px 11px; border-radius: 999px; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; background: #efe9dc; color: #52625c; }
.webp-badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.webp-badge[data-state="ok"] { background: #e3f1e8; color: #1f6b3f; }
.webp-badge[data-state="todo"] { background: #fbefd6; color: #94692f; }
.webp-badge[data-state="off"] { background: #fbe4e1; color: #a63d2f; }
.webp-card__btn { flex: none; display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 46px; padding: 0 22px; border: 1px solid #06211b; border-radius: 12px; background: #06211b; color: #fff; font-size: .92rem; font-weight: 600; white-space: nowrap; cursor: pointer; box-shadow: 0 12px 22px -14px rgba(10, 47, 38, .9); transition: background-color .2s, border-color .2s, box-shadow .2s; }
.webp-card__btn:hover:not(:disabled) { background: #0e3a2f; }
.webp-card__btn:focus-visible { outline: 3px solid rgba(217, 165, 102, .85); outline-offset: 2px; }
.webp-card__btn:disabled { cursor: not-allowed; border-color: #dcd0bc; background: #f3eee3; color: #8b958f; box-shadow: none; }
.webp-card__stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 20px 0 0; }
.webp-card__stats > div { padding: 14px 16px; border: 1px solid #ece4d6; border-radius: 14px; background: #fcfaf6; }
.webp-card__stats dt { margin: 0 0 5px; font-size: .68rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: #7a857f; }
.webp-card__stats dd { margin: 0; font-size: 1.3rem; font-weight: 700; line-height: 1.15; color: #06211b; }
.webp-card__status { margin: 16px 0 0; font-size: .93rem; line-height: 1.5; color: #52625c; }
.webp-card__progress { margin-top: 16px; padding: 16px 18px; border: 1px solid #ece4d6; border-radius: 14px; background: #fcfaf6; }
.webp-card__track { height: 10px; border-radius: 999px; background: #e9dfca; overflow: hidden; }
.webp-card__bar { height: 100%; width: 0; border-radius: 999px; background: linear-gradient(90deg, #06211b, #1f6b3f); transition: width .3s; }
.webp-card__line { margin: 10px 0 0; font-size: .9rem; line-height: 1.5; color: #52625c; }
.webp-card__line[data-state="done"] { color: #1f6b3f; font-weight: 600; }
.webp-card__line[data-state="error"] { color: #a63d2f; font-weight: 600; }
.webp-card__failed { margin: 10px 0 0; padding-left: 20px; font-size: .88rem; color: #a63d2f; list-style: disc; }
@media (max-width: 720px) {
    .webp-card { padding: 20px 18px; }
    .webp-card__top { flex-wrap: wrap; }
    .webp-card__btn { width: 100%; }
    .webp-card__stats { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) { .webp-card__bar, .webp-card__btn { transition: none; } }
</style>

<section class="webp-card" id="webp-card" aria-labelledby="webp-title">

    <div class="webp-card__top">
        <div class="webp-card__icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4.5" width="17" height="15" rx="2.5"/><circle cx="9" cy="10" r="1.7"/><path d="m4.5 17.5 5-4.8 3.6 3.3 2.4-2.2 4 3.7"/></svg>
        </div>

        <div class="webp-card__intro">
            <div class="webp-card__titlerow">
                <h2 id="webp-title" class="webp-card__title">Pictures as WebP</h2>
                <span id="webp-badge" class="webp-badge" data-state="load">Checking</span>
            </div>
            <p class="webp-card__text">
                Every picture you upload or replace is turned into a lighter WebP file automatically.
                This button does the same for the pictures that were uploaded earlier, and points the site at the new files.
                The original files are kept.
            </p>
        </div>

        <button type="button" id="webp-run" class="webp-card__btn" disabled>Convert old pictures to WebP</button>
    </div>

    <dl class="webp-card__stats">
        <div><dt>WebP pictures</dt><dd id="webp-s-webp">–</dd></div>
        <div><dt>Still JPG, PNG or GIF</dt><dd id="webp-s-left">–</dd></div>
        <div><dt>Server</dt><dd id="webp-s-server">–</dd></div>
    </dl>

    <p id="webp-status" class="webp-card__status" aria-live="polite">Checking the pictures …</p>

    <div id="webp-progress" class="webp-card__progress" hidden>
        <div class="webp-card__track"><div id="webp-bar" class="webp-card__bar"></div></div>
        <p id="webp-line" class="webp-card__line"></p>
    </div>

    <ul id="webp-failed" class="webp-card__failed" hidden></ul>
</section>

{{-- Confirmation: a proper dialog instead of the browser's plain pop-up --}}
<dialog id="webp-modal" class="webp-modal" aria-labelledby="webp-modal-title" aria-describedby="webp-modal-text">
    <form method="dialog" class="webp-modal__form">

        <header class="webp-modal__head">
            <div class="webp-modal__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4.5" width="17" height="15" rx="2.5"/><circle cx="9" cy="10" r="1.7"/><path d="m4.5 17.5 5-4.8 3.6 3.3 2.4-2.2 4 3.7"/></svg>
            </div>
            <div class="webp-modal__heading">
                <p class="webp-modal__eyebrow">Media library</p>
                <h3 id="webp-modal-title" class="webp-modal__title">Convert old pictures to WebP</h3>
            </div>
            <button type="submit" value="cancel" class="webp-modal__x" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </header>

        <div class="webp-modal__body">
            <p id="webp-modal-text" class="webp-modal__text">The pictures uploaded earlier become lighter WebP files, and the site starts using them right away.</p>

            <dl class="webp-modal__stats">
                <div><dt>Pictures</dt><dd id="webp-m-count">–</dd></div>
                <div><dt>Current size</dt><dd id="webp-m-size">–</dd></div>
                <div><dt>Originals</dt><dd>Kept</dd></div>
            </dl>

            <ul class="webp-modal__list">
                <li>Animated GIFs, favicons and e-mail pictures are left as they are.</li>
                <li>Every picture becomes WebP, so the whole site uses one format.</li>
                <li>Keep this page open until it says <em>Done</em>.</li>
            </ul>

            <p class="webp-modal__tip"><strong>Good to know</strong> It is wise to have a recent backup of the database first.</p>
        </div>

        <footer class="webp-modal__foot">
            <button type="submit" value="cancel" class="webp-modal__btn webp-modal__btn--ghost" autofocus>Cancel</button>
            <button type="submit" value="ok" class="webp-modal__btn webp-modal__btn--primary" id="webp-modal-ok">
                Convert now
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </footer>

    </form>
</dialog>

<style>
/* The admin's reset removes the browser's own centring of a dialog, so it is set here. */
.webp-modal { position: fixed; inset: 0; margin: auto; width: min(540px, calc(100vw - 32px)); height: fit-content; max-height: calc(100dvh - 32px); padding: 0; border: 0; border-radius: 20px; overflow: hidden; background: #fff; color: #06211b; box-shadow: 0 50px 100px -32px rgba(4, 24, 19, .75), 0 0 0 1px rgba(10, 47, 38, .06); }
.webp-modal::backdrop { background: rgba(4, 24, 19, .6); -webkit-backdrop-filter: blur(3px); backdrop-filter: blur(3px); }
.webp-modal[open] { animation: webp-pop .24s cubic-bezier(.22, 1, .36, 1); }
.webp-modal[open]::backdrop { animation: webp-fade .24s ease; }
.webp-modal__form { display: flex; flex-direction: column; max-height: calc(100dvh - 32px); margin: 0; }

.webp-modal__head { display: flex; align-items: center; gap: 16px; padding: 22px 24px 18px 26px; border-bottom: 1px solid #ece4d6; background: linear-gradient(180deg, #fffdf8, #fff); }
.webp-modal__icon { flex: none; display: grid; place-items: center; width: 48px; height: 48px; border-radius: 14px; background: #06211b; color: #dfc294; }
.webp-modal__heading { min-width: 0; flex: 1; }
.webp-modal__eyebrow { margin: 0 0 2px; font-size: .68rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #94692f; }
.webp-modal__title { margin: 0; font-size: 1.25rem; font-weight: 600; line-height: 1.25; color: #06211b; }
.webp-modal__x { flex: none; display: grid; place-items: center; width: 36px; height: 36px; padding: 0; border: 1px solid #dcd0bc; border-radius: 50%; background: #fff; color: #52625c; cursor: pointer; transition: border-color .2s, color .2s; }
.webp-modal__x:hover { border-color: #06211b; color: #06211b; }

.webp-modal__body { display: grid; gap: 18px; overflow-y: auto; padding: 22px 26px 24px; }
.webp-modal__text { margin: 0; font-size: .97rem; line-height: 1.6; color: #52625c; }

.webp-modal__stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 0; }
.webp-modal__stats > div { padding: 12px 14px; border: 1px solid #ece4d6; border-radius: 14px; background: #fcfaf6; }
.webp-modal__stats dt { margin: 0 0 4px; font-size: .66rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: #7a857f; }
.webp-modal__stats dd { margin: 0; font-size: 1.3rem; font-weight: 700; line-height: 1.1; color: #06211b; }

.webp-modal__list { display: grid; gap: 10px; margin: 0; padding: 0; list-style: none; }
.webp-modal__list li { position: relative; padding-left: 28px; font-size: .93rem; line-height: 1.5; color: #2f3b36; }
.webp-modal__list li::before { content: ""; position: absolute; left: 0; top: .16em; width: 18px; height: 18px; border-radius: 50%; background: #06211b url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23e6c28f' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7'/%3E%3C/svg%3E") center / 11px no-repeat; }

.webp-modal__tip { margin: 0; padding: 12px 14px 12px 16px; border-left: 3px solid #d9a566; border-radius: 4px 12px 12px 4px; background: #fbf5e8; font-size: .9rem; line-height: 1.5; color: #5c4a22; }
.webp-modal__tip strong { display: block; margin-bottom: 2px; font-size: .68rem; letter-spacing: .12em; text-transform: uppercase; color: #94692f; }

.webp-modal__foot { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 26px; border-top: 1px solid #ece4d6; background: #fcfaf6; }
.webp-modal__btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 44px; padding: 0 22px; border-radius: 12px; font-size: .92rem; font-weight: 600; cursor: pointer; transition: background-color .2s, border-color .2s, box-shadow .2s; }
.webp-modal__btn svg { transition: transform .2s; }
.webp-modal__btn--ghost { border: 1px solid #d8cdb6; background: #fff; color: #06211b; }
.webp-modal__btn--ghost:hover { border-color: #06211b; }
.webp-modal__btn--primary { border: 1px solid #06211b; background: #06211b; color: #fff; box-shadow: 0 10px 20px -12px rgba(10, 47, 38, .9); }
.webp-modal__btn--primary:hover { background: #0e3a2f; }
.webp-modal__btn--primary:hover svg { transform: translateX(3px); }
.webp-modal__btn:focus-visible, .webp-modal__x:focus-visible { outline: 3px solid rgba(217, 165, 102, .85); outline-offset: 2px; }

@keyframes webp-pop { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: none; } }
@keyframes webp-fade { from { opacity: 0; } to { opacity: 1; } }
@media (max-width: 480px) {
    .webp-modal__head { padding: 18px 16px 16px 18px; }
    .webp-modal__body { padding: 18px 18px 20px; }
    .webp-modal__stats dd { font-size: 1.05rem; }
    .webp-modal__foot { flex-direction: column-reverse; padding: 14px 18px; }
    .webp-modal__btn { width: 100%; }
}
@media (prefers-reduced-motion: reduce) { .webp-modal[open], .webp-modal[open]::backdrop { animation: none; } .webp-modal__btn svg { transition: none; } }
</style>

@php
    // Blade's @json() splits what is inside it at every comma, so the array is built here and passed as one variable.
    $webpUrls = ['status' => route('admin.media.webp.status'), 'run' => route('admin.media.webp.run')];
@endphp

<script>
(function () {
    var urls = @json($webpUrls);
    var token = @json(csrf_token());
    var $ = function (id) { return document.getElementById(id); };
    var statusEl = $('webp-status'), button = $('webp-run'), box = $('webp-progress'), bar = $('webp-bar'),
        line = $('webp-line'), failedList = $('webp-failed');

    function size(bytes) {
        if (bytes <= 0) return '0 KB';
        return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    var dialog = document.getElementById('webp-modal');
    var lastInfo = null;

    function show(info) {
        lastInfo = info;

        var badge = $('webp-badge');
        var setBadge = function (state, text) { badge.setAttribute('data-state', state); badge.textContent = text; };

        $('webp-s-webp').textContent = info.webp;
        $('webp-s-left').textContent = info.pending ? info.pending + ' (' + size(info.pending_bytes) + ')' : '0';
        $('webp-s-server').textContent = info.can ? 'WebP ready' : 'Not available';

        if (!info.can) {
            setBadge('off', 'Not available');
            statusEl.textContent = 'This server cannot create WebP pictures yet. Ask the host to enable the PHP "gd" extension with WebP support. Until then, uploads are saved as they are.';
            button.disabled = true;
            return;
        }
        if (info.pending === 0) {
            setBadge('ok', 'All set');
            statusEl.textContent = 'Every picture that can be converted is already WebP. New uploads are converted automatically.';
            button.textContent = 'Nothing left to convert';
            button.disabled = true;
            return;
        }
        setBadge('todo', info.pending + ' to convert');
        statusEl.textContent = info.pending + ' picture(s) (' + size(info.pending_bytes) + ') are still JPG, PNG or GIF. Press the button to convert them.';
        button.textContent = 'Convert old pictures to WebP';
        button.disabled = false;
    }

    function load() {
        return fetch(urls.status, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(show)
            .catch(function () { statusEl.textContent = 'Could not check the pictures. Reload the page and try again.'; });
    }

    function post(offset) {
        return fetch(urls.run, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ offset: offset, limit: 12 })
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (json) {
                if (!r.ok) throw new Error(json.message || 'Something went wrong. Please try again.');
                return json;
            });
        });
    }

    button.addEventListener('click', function () {
        if (!dialog || typeof dialog.showModal !== 'function') {
            if (window.confirm('Convert the old pictures to WebP now? The original files are kept.')) start();
            return;
        }

        document.getElementById('webp-m-count').textContent = lastInfo ? lastInfo.pending : '–';
        document.getElementById('webp-m-size').textContent = lastInfo ? size(lastInfo.pending_bytes) : '–';
        dialog.returnValue = '';
        dialog.showModal();
    });

    if (dialog) {
        dialog.addEventListener('close', function () { if (dialog.returnValue === 'ok') start(); });
        // a click on the dark area around the box closes it
        dialog.addEventListener('click', function (e) { if (e.target === dialog) dialog.close('cancel'); });
    }

    function start() {
        var totals = { converted: 0, saved: 0, rows: 0, failed: [] };
        button.disabled = true;
        box.hidden = false;
        failedList.hidden = true;
        failedList.innerHTML = '';
        line.removeAttribute('data-state');
        bar.style.width = '0';

        (function step(offset) {
            post(offset).then(function (res) {
                totals.converted += res.converted;
                totals.saved += res.saved;
                totals.rows += res.rows;
                totals.failed = totals.failed.concat(res.failed || []);

                var percent = res.total ? Math.min(100, Math.round(res.next / res.total * 100)) : 100;
                bar.style.width = percent + '%';
                line.textContent = 'Working … ' + Math.min(res.next, res.total) + ' of ' + res.total + ' pictures checked';

                if (!res.done) return step(res.next);

                line.setAttribute('data-state', 'done');
                line.textContent = 'Done: ' + totals.converted + ' picture(s) converted, ' + size(totals.saved) + ' saved, ' + totals.rows + ' place(s) in the site content updated. Reload the page to see the new files.';
                totals.failed.forEach(function (path) {
                    var li = document.createElement('li');
                    li.textContent = 'Could not convert: ' + path;
                    failedList.appendChild(li);
                });
                failedList.hidden = totals.failed.length === 0;
                return load();
            }).catch(function (error) {
                line.setAttribute('data-state', 'error');
                line.textContent = error.message + ' Pictures done so far are kept: press the button again to continue.';
                button.disabled = false;
            });
        })(0);
    }

    load();
})();
</script>
