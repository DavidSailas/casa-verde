// Hero film: the play button opens a cinematic lightbox with the YouTube video.
// - The player is only created on click (nothing from YouTube loads with the page).
// - It uses the privacy-friendly youtube-nocookie domain.
// - It is removed on close so the video always stops.
// - It asks YouTube for 1080p (see requestHd). YouTube treats this as a request,
//   not an order: on a slow connection it may still lower the quality.

document.addEventListener('DOMContentLoaded', () => {
    const triggers = document.querySelectorAll('[data-video-open]');
    if (!triggers.length) return;

    const CLOSE_MS = 340;
    const QUALITY = 'hd1080';
    let dialog = null;
    let stage = null;
    let frameEl = null;
    let lastTrigger = null;
    let closing = false;
    let hdTimers = [];

    // The "Book your stay" button in the lightbox reuses the hero's own booking button.
    const heroBook = document.querySelector('.hero .primary-button');
    const bookUrl = heroBook?.getAttribute('href') || '#contact';
    const bookText = heroBook?.textContent.replace(/\s+/g, ' ').trim() || 'Book your stay';
    const brand = document.querySelector('.hero .eyebrow')?.textContent.trim() || 'Casa Verde Cliff Resort & Spa';

    const esc = (s) => s.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

    const build = () => {
        dialog = document.createElement('dialog');
        dialog.className = 'cv-video';
        dialog.setAttribute('aria-label', 'Casa Verde film');
        dialog.innerHTML = `
            <div class="cv-video__panel">
                <header class="cv-video__head">
                    <div class="cv-video__titles">
                        <span class="cv-video__eyebrow">${esc(brand)}</span>
                        <span class="cv-video__title">The Casa Verde Film</span>
                    </div>
                    <button type="button" class="cv-video__close" aria-label="Close video">
                        <span class="cv-video__esc" aria-hidden="true">Esc</span>
                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
                            <path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </header>

                <div class="cv-video__frame">
                    <span class="cv-video__corner cv-video__corner--tl" aria-hidden="true"></span>
                    <span class="cv-video__corner cv-video__corner--br" aria-hidden="true"></span>
                    <div class="cv-video__stage">
                        <span class="cv-video__loader" aria-hidden="true"></span>
                    </div>
                </div>

                <footer class="cv-video__foot">
                    <a class="cv-video__book" href="${esc(bookUrl)}">${esc(bookText)}</a>
                    <a class="cv-video__fallback" target="_blank" rel="noopener noreferrer">Watch on YouTube</a>
                </footer>
            </div>
        `;
        document.body.appendChild(dialog);
        stage = dialog.querySelector('.cv-video__stage');
        frameEl = dialog.querySelector('.cv-video__frame');

        dialog.querySelector('.cv-video__close').addEventListener('click', close);

        // Booking from inside the film: close first, then let the link scroll/navigate.
        dialog.querySelector('.cv-video__book').addEventListener('click', () => close());

        // Click on the dark backdrop (outside the panel) closes it.
        dialog.addEventListener('click', (e) => {
            if (!e.target.closest('.cv-video__panel')) close();
        });

        // Esc: run our animated close instead of the instant native one.
        dialog.addEventListener('cancel', (e) => {
            e.preventDefault();
            close();
        });
    };

    // ---- Ask YouTube for 1080p -------------------------------------------------
    const send = (iframe, func, args = []) => {
        try {
            iframe.contentWindow?.postMessage(JSON.stringify({ event: 'command', func, args }), '*');
        } catch (_) { /* player not ready yet */ }
    };

    const requestHd = (iframe) => {
        send(iframe, 'setPlaybackQualityRange', [QUALITY, QUALITY]);
        send(iframe, 'setPlaybackQuality', [QUALITY]);
    };

    const startHdRequests = (iframe) => {
        // Tell the player we want its events, then ask for HD a few times:
        // it only accepts the request once the stream is actually playing.
        iframe.contentWindow?.postMessage(JSON.stringify({ event: 'listening', id: 1, channel: 'widget' }), '*');
        [300, 1200, 2500, 4500].forEach((ms) => hdTimers.push(window.setTimeout(() => requestHd(iframe), ms)));
    };

    window.addEventListener('message', (e) => {
        let host = '';
        try { host = new URL(e.origin).hostname; } catch (_) { return; }
        if (!/(^|\.)youtube(-nocookie)?\.com$/.test(host)) return;
        const iframe = stage?.querySelector('iframe');
        if (!iframe || e.source !== iframe.contentWindow) return;

        let data = e.data;
        if (typeof data === 'string') {
            try { data = JSON.parse(data); } catch (_) { return; }
        }
        const state = data?.info?.playerState;
        if (data?.event === 'onReady' || state === 1) requestHd(iframe); // 1 = playing
    });

    const clearHdTimers = () => {
        hdTimers.forEach(clearTimeout);
        hdTimers = [];
    };

    // ---- Open / close ----------------------------------------------------------
    const open = (trigger) => {
        const id = trigger.dataset.videoId;
        if (!id || closing) return;

        if (!dialog) build();
        lastTrigger = trigger;

        // Fresh player every time.
        clearHdTimers();
        stage.querySelector('iframe')?.remove();
        stage.classList.remove('is-ready');

        // Poster behind the spinner, so the player never flashes black.
        stage.style.backgroundImage = `url('https://i.ytimg.com/vi/${encodeURIComponent(id)}/maxresdefault.jpg'), url('https://i.ytimg.com/vi/${encodeURIComponent(id)}/hqdefault.jpg')`;

        const iframe = document.createElement('iframe');
        const params = new URLSearchParams({
            autoplay: '1',
            rel: '0',
            playsinline: '1',
            modestbranding: '1',
            color: 'white',
            iv_load_policy: '3',
            enablejsapi: '1',
            vq: QUALITY, // old hint, harmless; the API requests above do the real work
            origin: window.location.origin,
        });
        iframe.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(id)}?${params}`;
        iframe.title = 'Casa Verde Cliff Resort & Spa film';
        iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        iframe.addEventListener('load', () => {
            stage.classList.add('is-ready');
            startHdRequests(iframe);
        });
        stage.appendChild(iframe);

        dialog.querySelector('.cv-video__fallback').href = `https://www.youtube.com/watch?v=${encodeURIComponent(id)}`;

        // Animate from the clicked button's position on screen.
        const r = trigger.getBoundingClientRect();
        dialog.style.setProperty('--cv-from-x', `${r.left + r.width / 2 - window.innerWidth / 2}px`);
        dialog.style.setProperty('--cv-from-y', `${r.top + r.height / 2 - window.innerHeight / 2}px`);

        dialog.showModal();
        document.documentElement.classList.add('cv-video-lock');
        requestAnimationFrame(() => requestAnimationFrame(() => dialog.classList.add('is-open')));
        dialog.querySelector('.cv-video__close').focus({ preventScroll: true });
    };

    function close() {
        if (!dialog || !dialog.open || closing) return;
        closing = true;
        clearHdTimers();
        dialog.classList.remove('is-open');

        window.setTimeout(() => {
            dialog.close();
            stage.querySelector('iframe')?.remove(); // stops playback
            document.documentElement.classList.remove('cv-video-lock');
            closing = false;
            lastTrigger?.focus({ preventScroll: true });
        }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : CLOSE_MS);
    }

    triggers.forEach((t) => t.addEventListener('click', () => open(t)));
});
