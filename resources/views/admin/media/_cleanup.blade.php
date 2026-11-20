{{-- Clean up old pictures: nothing is deleted straight away. Pictures go to a trash that can be restored. --}}
<style>
.mc-card { margin-top: 20px; padding: 24px 26px; border: 1px solid #dcd0bc; border-radius: 20px; background: #fff; box-shadow: 0 18px 36px -30px rgba(6, 33, 27, .45); }
.mc-top { display: flex; align-items: flex-start; gap: 18px; }
.mc-icon { flex: none; display: grid; place-items: center; width: 48px; height: 48px; border-radius: 14px; background: #06211b; color: #dfc294; }
.mc-intro { min-width: 0; flex: 1; }
.mc-titlerow { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 12px; }
.mc-title { margin: 0; font-size: 1.2rem; font-weight: 600; line-height: 1.25; color: #06211b; }
.mc-text { margin: 8px 0 0; max-width: 46rem; font-size: .95rem; line-height: 1.6; color: #52625c; }
.mc-badge { display: inline-flex; align-items: center; gap: 7px; padding: 4px 11px; border-radius: 999px; background: #ece4d6; color: #52625c; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
.mc-badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.mc-badge[data-state="ok"] { background: #e3f1e8; color: #1f6b3f; }
.mc-badge[data-state="todo"] { background: #fbefd6; color: #94692f; }
.mc-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 20px 0 0; }
.mc-stats > div { padding: 14px 16px; border: 1px solid #ece4d6; border-radius: 14px; background: #fcfaf6; }
.mc-stats dt { margin: 0 0 5px; font-size: .68rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: #52625c; }
.mc-stats dd { margin: 0; font-size: 1.3rem; font-weight: 700; line-height: 1.15; color: #06211b; }
.mc-stats small { display: block; margin-top: 4px; font-size: .8rem; font-weight: 500; color: #52625c; }
.mc-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 18px; }
.mc-note { margin: 14px 0 0; font-size: .9rem; line-height: 1.5; color: #52625c; }
.mc-note[data-state="done"] { color: #1f6b3f; font-weight: 600; }
.mc-note[data-state="error"] { color: #a63d2f; font-weight: 600; }

/* Buttons and links shared by the card, the dialog and the pictures */
.mc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 44px; padding: 0 20px; border: 1px solid transparent; border-radius: 10px; font-size: .92rem; font-weight: 500; line-height: 1; white-space: nowrap; cursor: pointer; transition: background-color .2s, border-color .2s, color .2s, box-shadow .2s; }
.mc-btn:focus-visible, .mc-trash:focus-visible, .mc-link:focus-visible { outline: 2px solid #94692f; outline-offset: 2px; }
.mc-btn--primary { border-color: #06211b; background: #06211b; color: #fcfaf6; }
.mc-btn--primary:hover:not(:disabled) { background: #1c5646; border-color: #1c5646; }
.mc-btn--ghost { border-color: #dcd0bc; background: #fff; color: #06211b; }
.mc-btn--ghost:hover:not(:disabled) { border-color: #1c5646; background: #eef4f0; }
.mc-btn--danger { border-color: #a63d2f; background: #a63d2f; color: #fff; }
.mc-btn--danger:hover:not(:disabled) { background: #8a2f23; border-color: #8a2f23; }
.mc-btn--quiet { padding: 0 14px; color: #a63d2f; background: transparent; }
.mc-btn--quiet:hover:not(:disabled) { background: #fbefec; }
.mc-btn:disabled { cursor: not-allowed; border-color: #ece4d6; background: #f7f2ea; color: #9aa6a1; }

.mc-link { font-size: .85rem; color: #1c5646; text-decoration: none; }
.mc-link:hover { text-decoration: underline; text-underline-offset: 3px; }
.mc-trash { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border: 0; border-radius: 8px; background: transparent; color: #a63d2f; font-size: .85rem; font-weight: 500; cursor: pointer; transition: background-color .2s; }
.mc-trash:hover { background: #fbefec; }
.mc-pic-actions { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 8px; padding-top: 12px; border-top: 1px solid #ece4d6; }
.mc-pic-note { font-size: .8rem; color: #52625c; }

/* Dialog */
.mc-modal { position: fixed; inset: 0; margin: auto; width: min(500px, calc(100vw - 32px)); height: fit-content; max-height: calc(100dvh - 32px); padding: 0; border: 0; border-radius: 20px; overflow: hidden; background: #fff; color: #06211b; box-shadow: 0 50px 100px -32px rgba(4, 24, 18, .75), 0 0 0 1px rgba(6, 33, 27, .06); }
.mc-modal::backdrop { background: rgba(4, 24, 18, .6); -webkit-backdrop-filter: blur(3px); backdrop-filter: blur(3px); }
.mc-modal[open] { animation: mc-pop .24s cubic-bezier(.22, 1, .36, 1); }
.mc-modal[open]::backdrop { animation: mc-fade .24s ease; }
.mc-modal__form { display: flex; flex-direction: column; max-height: calc(100dvh - 32px); margin: 0; }
.mc-modal__head { display: flex; align-items: center; gap: 16px; padding: 22px 24px 18px 26px; border-bottom: 1px solid #ece4d6; }
.mc-modal__icon { flex: none; display: grid; place-items: center; width: 48px; height: 48px; border-radius: 14px; background: #06211b; color: #dfc294; }
.mc-modal__icon[data-danger="true"] { background: #fbefec; color: #a63d2f; }
.mc-modal__heading { min-width: 0; flex: 1; }
.mc-modal__eyebrow { margin: 0 0 2px; font-size: .68rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #94692f; }
.mc-modal__title { margin: 0; font-size: 1.25rem; font-weight: 600; line-height: 1.25; color: #06211b; }
.mc-modal__x { flex: none; display: grid; place-items: center; width: 36px; height: 36px; padding: 0; border: 1px solid #dcd0bc; border-radius: 50%; background: #fff; color: #52625c; cursor: pointer; }
.mc-modal__x:hover { border-color: #06211b; color: #06211b; }
.mc-modal__body { display: grid; gap: 16px; overflow-y: auto; padding: 22px 26px 24px; }
.mc-modal__text { margin: 0; font-size: .97rem; line-height: 1.6; color: #52625c; }
.mc-modal__thumb { display: flex; align-items: center; gap: 14px; padding: 10px; border: 1px solid #ece4d6; border-radius: 14px; background: #fcfaf6; }
.mc-modal__thumb img { width: 72px; height: 72px; flex: none; border-radius: 10px; object-fit: contain; background: conic-gradient(#efeae0 25%, #fff 0 50%, #efeae0 0 75%, #fff 0) 0 0 / 12px 12px; }
.mc-modal__thumb span { min-width: 0; font-size: .85rem; line-height: 1.4; color: #12241f; overflow-wrap: anywhere; }
.mc-modal__stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin: 0; }
.mc-modal__stats > div { padding: 12px 14px; border: 1px solid #ece4d6; border-radius: 14px; background: #fcfaf6; }
.mc-modal__stats dt { margin: 0 0 4px; font-size: .66rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: #52625c; }
.mc-modal__stats dd { margin: 0; font-size: 1.2rem; font-weight: 700; line-height: 1.1; color: #06211b; }
.mc-modal__tip { margin: 0; padding: 12px 14px 12px 16px; border-left: 3px solid #c9a06a; border-radius: 4px 12px 12px 4px; background: #fbf5e8; font-size: .9rem; line-height: 1.5; color: #5c4a22; }
.mc-modal__tip[data-danger="true"] { border-left-color: #a63d2f; background: #fbefec; color: #6e2a21; }
.mc-modal__foot { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 26px; border-top: 1px solid #ece4d6; background: #fcfaf6; }

/* Message after an action */
.mc-toast { position: fixed; left: 50%; bottom: 24px; z-index: 60; display: none; align-items: center; gap: 14px; max-width: calc(100vw - 32px); padding: 12px 14px 12px 18px; border-radius: 12px; background: #06211b; color: #fcfaf6; font-size: .92rem; box-shadow: 0 24px 40px -20px rgba(4, 24, 18, .7); transform: translateX(-50%); }
.mc-toast.is-on { display: flex; animation: mc-rise .25s ease; }
.mc-toast button { padding: 6px 10px; border: 0; border-radius: 8px; background: rgba(255, 255, 255, .12); color: #dfc294; font-weight: 600; cursor: pointer; }
.mc-toast button:hover { background: rgba(255, 255, 255, .2); }

@keyframes mc-pop { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: none; } }
@keyframes mc-fade { from { opacity: 0; } to { opacity: 1; } }
@keyframes mc-rise { from { opacity: 0; transform: translate(-50%, 10px); } to { opacity: 1; transform: translateX(-50%); } }
@media (max-width: 720px) {
    .mc-card { padding: 20px 18px; }
    .mc-top { flex-wrap: wrap; }
    .mc-stats { grid-template-columns: 1fr; }
    .mc-actions > .mc-btn { flex: 1 1 100%; }
}
@media (max-width: 480px) {
    .mc-modal__head { padding: 18px 16px 16px 18px; }
    .mc-modal__body { padding: 18px 18px 20px; }
    .mc-modal__foot { flex-direction: column-reverse; padding: 14px 18px; }
    .mc-modal__foot .mc-btn { width: 100%; }
}
@media (prefers-reduced-motion: reduce) { .mc-modal[open], .mc-modal[open]::backdrop, .mc-toast.is-on { animation: none; } }
</style>

<section class="mc-card" id="cleanup-card" aria-labelledby="mc-title">

    <div class="mc-top">
        <div class="mc-icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12.2a1.8 1.8 0 0 0 1.8 1.8h6.4a1.8 1.8 0 0 0 1.8-1.8L18 7M9 7V4.8A.8.8 0 0 1 9.8 4h4.4a.8.8 0 0 1 .8.8V7"/></svg>
        </div>

        <div class="mc-intro">
            <div class="mc-titlerow">
                <h2 id="mc-title" class="mc-title">Clean up old pictures</h2>
                <span id="mc-badge" class="mc-badge" data-state="load">Checking</span>
            </div>
            <p class="mc-text">
                Once the pictures are WebP, the old JPG and PNG copies, and pictures that a newer upload replaced, only take up space.
                Pictures the site uses are never touched. Nothing is deleted straight away: removed pictures wait in the trash, and you can restore them.
            </p>
        </div>
    </div>

    <dl class="mc-stats">
        <div><dt>Old JPG/PNG photos</dt><dd id="mc-s-webp">–</dd><small id="mc-s-webp-size">&nbsp;</small></div>
        <div><dt>Replaced uploads</dt><dd id="mc-s-older">–</dd><small id="mc-s-older-size">&nbsp;</small></div>
        <div><dt>In the trash</dt><dd id="mc-s-trash">–</dd><small id="mc-s-trash-size">&nbsp;</small></div>
    </dl>

    <div class="mc-actions">
        <button type="button" class="mc-btn mc-btn--primary" id="mc-move" disabled>Move to trash</button>
        <button type="button" class="mc-btn mc-btn--ghost" id="mc-restore" disabled>Restore all</button>
        <button type="button" class="mc-btn mc-btn--quiet" id="mc-empty" disabled>Empty trash</button>
    </div>

    <p id="mc-note" class="mc-note" aria-live="polite"></p>
</section>

<dialog id="mc-modal" class="mc-modal" aria-labelledby="mc-modal-title" aria-describedby="mc-modal-text">
    <form method="dialog" class="mc-modal__form">
        <header class="mc-modal__head">
            <div class="mc-modal__icon" id="mc-modal-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12.2a1.8 1.8 0 0 0 1.8 1.8h6.4a1.8 1.8 0 0 0 1.8-1.8L18 7M9 7V4.8A.8.8 0 0 1 9.8 4h4.4a.8.8 0 0 1 .8.8V7"/></svg>
            </div>
            <div class="mc-modal__heading">
                <p class="mc-modal__eyebrow">Media library</p>
                <h3 id="mc-modal-title" class="mc-modal__title"></h3>
            </div>
            <button type="submit" value="cancel" class="mc-modal__x" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </header>

        <div class="mc-modal__body">
            <p id="mc-modal-text" class="mc-modal__text"></p>
            <div class="mc-modal__thumb" id="mc-modal-thumb" hidden><img src="" alt=""><span></span></div>
            <dl class="mc-modal__stats" id="mc-modal-stats" hidden></dl>
            <p class="mc-modal__tip" id="mc-modal-tip" hidden></p>
        </div>

        <footer class="mc-modal__foot">
            <button type="submit" value="cancel" class="mc-btn mc-btn--ghost" autofocus>Cancel</button>
            <button type="submit" value="ok" class="mc-btn mc-btn--primary" id="mc-modal-ok">Confirm</button>
        </footer>
    </form>
</dialog>

<div class="mc-toast" id="mc-toast" role="status" aria-live="polite"><span id="mc-toast-text"></span><button type="button" id="mc-toast-undo" hidden>Undo</button></div>

@php
    // Blade's @json() splits what is inside it at every comma, so the array is built here and passed as one variable.
    $mcUrls = [
        'status' => route('admin.media.cleanup.status'),
        'move' => route('admin.media.cleanup.move'),
        'restore' => route('admin.media.cleanup.restore'),
        'empty' => route('admin.media.cleanup.empty'),
    ];
@endphp

<script>
(function () {
    var urls = @json($mcUrls);
    var token = @json(csrf_token());
    var $ = function (id) { return document.getElementById(id); };
    var info = null;

    function size(bytes) {
        if (bytes <= 0) return '0 KB';
        return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }
    function plural(n, word) { return n + ' ' + word + (n === 1 ? '' : 's'); }

    function api(url, method, body) {
        return fetch(url, {
            method: method,
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: body ? JSON.stringify(body) : undefined
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (json) {
                if (!r.ok) throw new Error(json.message || 'Something went wrong. Please try again.');
                return json;
            });
        });
    }

    function note(text, state) {
        $('mc-note').textContent = text || '';
        if (state) $('mc-note').setAttribute('data-state', state); else $('mc-note').removeAttribute('data-state');
    }

    /* ---------- the card ---------- */
    function render(data) {
        info = data;
        var removable = data.webp.count + data.older.count;
        var badge = $('mc-badge');

        $('mc-s-webp').textContent = data.webp.count;
        $('mc-s-webp-size').textContent = data.webp.count ? size(data.webp.bytes) + ' to free' : 'Nothing to clean';
        $('mc-s-older').textContent = data.older.count;
        $('mc-s-older-size').textContent = data.older.count ? size(data.older.bytes) + ' to free' : 'Nothing to clean';
        $('mc-s-trash').textContent = data.trash.count;
        $('mc-s-trash-size').textContent = data.trash.count ? size(data.trash.bytes) : 'Empty';

        badge.setAttribute('data-state', removable ? 'todo' : 'ok');
        badge.textContent = removable ? plural(removable, 'picture') + ' to clean' : 'All clean';

        $('mc-move').textContent = removable ? 'Move ' + removable + ' to trash' : 'Nothing to clean up';
        $('mc-move').disabled = removable === 0;
        $('mc-restore').disabled = data.trash.count === 0;
        $('mc-empty').disabled = data.trash.count === 0;
    }

    function load() {
        return api(urls.status, 'GET').then(render).catch(function () {
            $('mc-badge').textContent = 'Unavailable';
            note('Could not check the pictures. Reload the page and try again.', 'error');
        });
    }

    /* ---------- the dialog ---------- */
    var dialog = $('mc-modal');

    function ask(opts) {
        return new Promise(function (resolve) {
            $('mc-modal-title').textContent = opts.title;
            $('mc-modal-text').textContent = opts.text;
            $('mc-modal-icon').setAttribute('data-danger', opts.danger ? 'true' : 'false');

            var thumb = $('mc-modal-thumb');
            thumb.hidden = !opts.thumb;
            if (opts.thumb) { thumb.querySelector('img').src = opts.thumb.src; thumb.querySelector('span').textContent = opts.thumb.name; }

            var stats = $('mc-modal-stats');
            stats.innerHTML = '';
            (opts.stats || []).forEach(function (pair) {
                var box = document.createElement('div');
                var dt = document.createElement('dt'); dt.textContent = pair[0];
                var dd = document.createElement('dd'); dd.textContent = pair[1];
                box.appendChild(dt); box.appendChild(dd); stats.appendChild(box);
            });
            stats.hidden = !(opts.stats && opts.stats.length);

            var tip = $('mc-modal-tip');
            tip.hidden = !opts.tip;
            tip.textContent = opts.tip || '';
            tip.setAttribute('data-danger', opts.danger ? 'true' : 'false');

            var ok = $('mc-modal-ok');
            ok.textContent = opts.confirm;
            ok.className = 'mc-btn ' + (opts.danger ? 'mc-btn--danger' : 'mc-btn--primary');

            if (typeof dialog.showModal !== 'function') { resolve(window.confirm(opts.title + '\n\n' + opts.text)); return; }

            dialog.returnValue = '';
            var done = function () { dialog.removeEventListener('close', done); resolve(dialog.returnValue === 'ok'); };
            dialog.addEventListener('close', done);
            dialog.showModal();
        });
    }
    // a click on the dark area around the box closes it
    dialog.addEventListener('click', function (e) { if (e.target === dialog) dialog.close('cancel'); });

    /* ---------- the message at the bottom ---------- */
    var toastTimer = null;
    function toast(text, undo) {
        var box = $('mc-toast'), button = $('mc-toast-undo');
        $('mc-toast-text').textContent = text;
        button.hidden = !undo;
        button.onclick = undo ? function () { box.classList.remove('is-on'); undo(); } : null;
        box.classList.add('is-on');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { box.classList.remove('is-on'); }, 8000);
    }

    /* ---------- buttons on the card ---------- */
    $('mc-move').addEventListener('click', function () {
        if (!info) return;
        var count = info.webp.count + info.older.count;
        ask({
            title: 'Move ' + plural(count, 'picture') + ' to the trash?',
            text: 'These pictures are not used anywhere on the site: the old JPG/PNG copies of photos that now exist as WebP, and uploads that a newer upload replaced.',
            stats: [['Pictures', String(count)], ['Space freed', size(info.webp.bytes + info.older.bytes)]],
            tip: 'Nothing is deleted yet. The pictures wait in the trash, and "Restore all" brings them back.',
            confirm: 'Move to trash'
        }).then(function (ok) {
            if (!ok) return;
            $('mc-move').disabled = true;
            note('Moving …');
            api(urls.move, 'POST', {}).then(function (res) {
                note(plural(res.moved, 'picture') + ' moved to the trash, ' + size(res.bytes) + ' freed. Reload the page to update the list below.', 'done');
                return load();
            }).catch(function (e) { note(e.message, 'error'); load(); });
        });
    });

    $('mc-restore').addEventListener('click', function () {
        ask({
            title: 'Restore everything from the trash?',
            text: 'All pictures in the trash go back to the folders they came from.',
            stats: [['Pictures', String(info.trash.count)], ['Size', size(info.trash.bytes)]],
            confirm: 'Restore all'
        }).then(function (ok) {
            if (!ok) return;
            api(urls.restore, 'POST', {}).then(function (res) {
                note(plural(res.restored, 'picture') + ' restored.' + (res.skipped.length ? ' ' + plural(res.skipped.length, 'picture') + ' could not be restored because a file with that name exists.' : ''), 'done');
                return load();
            }).catch(function (e) { note(e.message, 'error'); });
        });
    });

    $('mc-empty').addEventListener('click', function () {
        ask({
            title: 'Empty the trash for good?',
            text: 'The pictures in the trash will be deleted permanently. This cannot be undone.',
            stats: [['Pictures', String(info.trash.count)], ['Space freed', size(info.trash.bytes)]],
            tip: 'Make sure the live site still looks right before you do this.',
            danger: true,
            confirm: 'Delete permanently'
        }).then(function (ok) {
            if (!ok) return;
            api(urls.empty, 'POST', {}).then(function (res) {
                note(plural(res.deleted, 'picture') + ' deleted, ' + size(res.bytes) + ' freed.', 'done');
                return load();
            }).catch(function (e) { note(e.message, 'error'); });
        });
    });

    /* ---------- "Move to trash" on a single picture ---------- */
    document.addEventListener('click', function (e) {
        var button = e.target.closest('[data-trash-path]');
        if (!button) return;

        var path = button.getAttribute('data-trash-path');
        var risky = button.getAttribute('data-trash-risky') === '1';

        ask({
            title: 'Move this picture to the trash?',
            text: 'Nothing on the site uses it. It waits in the trash, so you can restore it if you need it again.',
            thumb: { src: button.getAttribute('data-trash-src'), name: path },
            tip: risky ? 'Icons and symbols are sometimes picked by the site by name. If something looks missing afterwards, restore it with "Restore all".' : '',
            confirm: 'Move to trash'
        }).then(function (ok) {
            if (!ok) return;
            api(urls.move, 'POST', { paths: [path] }).then(function (res) {
                if (!res.moved) { toast('This picture is in use, so it was not moved.'); return; }
                var card = button.closest('[data-media-card]');
                if (card) card.remove();
                toast('Moved to the trash.', function () {
                    api(urls.restore, 'POST', { paths: [path] }).then(function () { window.location.reload(); });
                });
                load();
            }).catch(function (err) { toast(err.message); });
        });
    });

    load();
})();
</script>
