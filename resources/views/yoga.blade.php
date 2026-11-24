<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261017">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.seo', ['seoPage' => 'yoga-reset', 'seoTitle' => 'Yoga Reset'])
    @vite([
        'resources/css/header.css',
        'resources/css/pages.css',
        'resources/css/yoga.css',
        'resources/css/theme.css',
        'resources/css/footer.css','resources/css/rule.css', 'resources/css/responsive.css',
        'resources/js/app.js',
        'resources/css/yoga-v2.css'
    ])



</head>
<body data-no-photo-viewer>
    @include('layouts.header')
    @php
        $img = fn (?string $path, string $fallback = 'images/main.jpg') => asset($path && file_exists(public_path($path)) ? $path : $fallback);
        // A button that still points at the starting placeholder (/#contact) opens the Yoga booking page instead.
        // A button the admin has pointed somewhere else keeps that address.
        $href = function (?string $url, ?string $book = null, ?string $session = null) {
            if ($book !== null && in_array(trim((string) $url), ['', '/#contact'], true)) {
                return route('yoga.book', array_filter(['type' => $book, 'session' => $session]));
            }

            return \Illuminate\Support\Str::startsWith($url ?? '', ['http', '#', 'mailto:', 'tel:']) ? $url : url($url ?: '/#contact');
        };
        $logo = \App\Models\HomeContent::siteLogo();
    @endphp

    <main class="cv-page yg">

        {{-- Top banner --}}
        <section class="yg-hero" style="background-image:url('{{ $img($page['hero']['image']) }}')">
            <div class="yg-wrap">
                <div class="yg-hero__body">
                    <span class="yg-eyebrow">{{ $page['hero']['eyebrow'] }}</span>

                    @if(count($page['chips']))
                        <ul class="yg-chips" role="list">
                            @foreach($page['chips'] as $chip)
                                <li class="yg-chip yg-chip--{{ $chip['style'] }}"><span class="yg-dot" aria-hidden="true"></span>{{ $chip['text'] }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <h1>{{ $page['hero']['title'] }}</h1>
                    <p class="yg-hero__lead">{{ $page['hero']['text'] }}</p>

                    <div class="yg-actions">
                        @if($page['hero']['button_text'])
                            <a href="{{ $href($page['hero']['button_url'], 'retreat') }}" class="yg-btn yg-btn--gold">{{ $page['hero']['button_text'] }} <span aria-hidden="true">→</span></a>
                        @endif
                        @if($page['hero_link'])
                            <a href="{{ $href($page['hero_link']['url']) }}" class="yg-btn yg-btn--outline">{{ $page['hero_link']['text'] }}</a>
                        @endif
                    </div>

                    @if(count($page['facts']))
                        <ul class="yg-facts" role="list">
                            @foreach($page['facts'] as $fact)
                                <li>{{ $fact }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>

        {{-- The blocks below follow the order and visibility set in the admin (Yoga Reset > Page layout) --}}
        @foreach($page['layout']['flow'] as $block)
            @include('yoga.blocks.'.$block)
        @endforeach
    </main>

    <script>
        // Day tabs and the photo slider. Without JavaScript every day is listed and the photos scroll by hand.
        (function () {
            var root = document.querySelector('.yg');
            if (!root) { return; }
            root.classList.add('yg-js');

            // Day tabs
            root.querySelectorAll('[data-yg-tabs]').forEach(function (box) {
                var tabs = [].slice.call(box.querySelectorAll('[data-yg-tab]'));
                var panels = [].slice.call(box.querySelectorAll('[data-yg-panel]'));
                if (!tabs.length || tabs.length !== panels.length) { return; }

                function show(index, focus) {
                    tabs.forEach(function (tab, i) {
                        var on = i === index;
                        tab.classList.toggle('is-active', on);
                        tab.setAttribute('aria-selected', on ? 'true' : 'false');
                        tab.tabIndex = on ? 0 : -1;
                        panels[i].hidden = !on;
                        if (on && focus) { tab.focus(); }
                    });
                }

                tabs.forEach(function (tab, i) {
                    tab.addEventListener('click', function () { show(i, false); });
                    tab.addEventListener('keydown', function (e) {
                        var next = null;
                        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { next = (i + 1) % tabs.length; }
                        if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { next = (i - 1 + tabs.length) % tabs.length; }
                        if (e.key === 'Home') { next = 0; }
                        if (e.key === 'End') { next = tabs.length - 1; }
                        if (next !== null) { e.preventDefault(); show(next, true); }
                    });
                });

                box.querySelector('[data-yg-tablist]').hidden = false;
                show(0, false);
            });

            // Photo slider
            root.querySelectorAll('[data-yg-slider]').forEach(function (slider) {
                var slides = [].slice.call(slider.children);
                var wrap = slider.closest('section');
                var count = wrap.querySelector('[data-yg-count]');
                var prev = wrap.querySelector('[data-yg-prev]');
                var next = wrap.querySelector('[data-yg-next]');

                function step() { return slides[0] ? slides[0].getBoundingClientRect().width + 16 : 300; }
                function current() { return Math.min(slides.length, Math.round(slider.scrollLeft / step()) + 1); }
                function update() {
                    if (count) { count.textContent = current() + ' / ' + slides.length; }
                    if (prev) { prev.disabled = slider.scrollLeft <= 4; }
                    if (next) { next.disabled = slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 4; }
                }

                if (prev) { prev.addEventListener('click', function () { slider.scrollBy({ left: -step(), behavior: 'smooth' }); }); }
                if (next) { next.addEventListener('click', function () { slider.scrollBy({ left: step(), behavior: 'smooth' }); }); }
                slider.addEventListener('scroll', function () { window.requestAnimationFrame(update); }, { passive: true });
                window.addEventListener('resize', update);
                update();
            });
        })();
    </script>

    <script>
        // Gallery: drag with the mouse, progress bar. Photo viewer: caption, hint, smooth fade, move-to-pan when zoomed.
        (function () {
            document.querySelectorAll('[data-yg-slider]').forEach(function (slider) {
                var wrap = slider.closest('section');
                var count = wrap && wrap.querySelector('[data-yg-count]');

                // Controls bar under the photos: progress line, counter and the two arrows
                var prevBtn = wrap && wrap.querySelector('[data-yg-prev]');
                var nextBtn = wrap && wrap.querySelector('[data-yg-next]');
                var host = count ? count.parentNode : null;
                if (prevBtn && nextBtn && host && !host.querySelector('.yg-controls')) {
                    var controls = document.createElement('div');
                    controls.className = 'yg-controls';
                    var track = document.createElement('div');
                    track.className = 'yg-progress';
                    track.setAttribute('aria-hidden', 'true');
                    track.innerHTML = '<span></span>';
                    var buttons = document.createElement('div');
                    buttons.className = 'yg-controls__btns';
                    prevBtn.classList.add('yg-nav');
                    nextBtn.classList.add('yg-nav');
                    buttons.appendChild(prevBtn);
                    buttons.appendChild(nextBtn);
                    controls.appendChild(track);
                    controls.appendChild(count);
                    controls.appendChild(buttons);
                    host.appendChild(controls);
                    var oldBox = wrap.querySelector('.yg-gallery__head .yg-arrows');
                    if (oldBox && !oldBox.children.length) { oldBox.remove(); }
                }

                if (count) {
                    var bar = wrap.querySelector('.yg-progress');
                    if (!bar) {
                        bar = document.createElement('div');
                        bar.className = 'yg-progress';
                        bar.setAttribute('aria-hidden', 'true');
                        bar.innerHTML = '<span></span>';
                        count.parentNode.insertBefore(bar, count);
                    }
                    var fill = bar.firstChild;
                    var update = function () {
                        var max = slider.scrollWidth - slider.clientWidth;
                        var share = slider.scrollWidth ? slider.clientWidth / slider.scrollWidth : 1;
                        var pos = max > 0 ? slider.scrollLeft / max : 0;
                        fill.style.width = (share * 100) + '%';
                        fill.style.marginLeft = (pos * (1 - share) * 100) + '%';
                    };
                    slider.addEventListener('scroll', function () { window.requestAnimationFrame(update); }, { passive: true });
                    window.addEventListener('resize', update);
                    update();
                }

                var down = false, moved = false, startX = 0, startLeft = 0;
                slider.addEventListener('pointerdown', function (e) {
                    if (e.pointerType !== 'mouse' || e.button !== 0) { return; }
                    down = true; moved = false; startX = e.clientX; startLeft = slider.scrollLeft;
                });
                window.addEventListener('pointermove', function (e) {
                    if (!down) { return; }
                    var dx = e.clientX - startX;
                    if (Math.abs(dx) > 5) { moved = true; slider.classList.add('is-dragging'); }
                    if (moved) { slider.scrollLeft = startLeft - dx; }
                });
                window.addEventListener('pointerup', function () {
                    if (!down) { return; }
                    down = false;
                    slider.classList.remove('is-dragging');
                });
                slider.addEventListener('click', function (e) {
                    if (moved) { e.stopPropagation(); e.preventDefault(); moved = false; }
                }, true);
                slider.addEventListener('dragstart', function (e) { e.preventDefault(); });
            });

            function enhance(viewer) {
                if (viewer.__cvv) { return; }
                viewer.__cvv = true;
                var img = viewer.querySelector('.cv-photo-viewer__image');
                if (!img) { return; }

                var caption = document.createElement('p');
                caption.className = 'cv-photo-viewer__caption';
                caption.hidden = true;
                var hint = document.createElement('p');
                hint.className = 'cv-photo-viewer__hint';
                hint.setAttribute('aria-hidden', 'true');
                hint.textContent = 'Use the arrow keys to browse · Esc to close';
                viewer.appendChild(caption);
                viewer.appendChild(hint);

                var last = null;
                var done = function () { img.classList.remove('is-loading'); };
                img.addEventListener('load', done);
                img.addEventListener('error', done);

                new MutationObserver(function () {
                    var src = img.getAttribute('src');
                    if (src && src !== last) {
                        img.classList.add('is-loading');
                        window.setTimeout(done, 700);
                    }
                    last = src;
                    var alt = (img.getAttribute('alt') || '').trim();
                    caption.textContent = /^resort photo$/i.test(alt) ? '' : alt;
                    caption.hidden = !caption.textContent;
                }).observe(img, { attributes: true, attributeFilter: ['src', 'alt'] });

                viewer.addEventListener('mousemove', function (e) {
                    if (!img.classList.contains('is-zoomed')) { return; }
                    var box = viewer.getBoundingClientRect();
                    img.style.transformOrigin = ((e.clientX - box.left) / box.width * 100) + '% ' + ((e.clientY - box.top) / box.height * 100) + '%';
                });
            }

            function find() {
                var viewer = document.querySelector('.cv-photo-viewer');
                if (viewer) { enhance(viewer); }
            }
            find();
            new MutationObserver(find).observe(document.body, { childList: true });
            document.addEventListener('DOMContentLoaded', find);
        })();
    </script>

    @include('layouts.footer')
</body>
</html>
