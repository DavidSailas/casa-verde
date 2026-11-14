<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261017">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yoga Reset | Casa Verde Cliff Resort &amp; Spa</title>
    @vite([
        'resources/css/header.css',
        'resources/css/pages.css',
        'resources/css/yoga.css',
        'resources/css/theme.css',
        'resources/css/footer.css','resources/css/rule.css',
        'resources/js/app.js'
    ])
</head>
<body>
    @include('layouts.header')
    @php
        $img = fn (?string $path, string $fallback = 'images/main.jpg') => asset($path && file_exists(public_path($path)) ? $path : $fallback);
        $href = fn (?string $url) => \Illuminate\Support\Str::startsWith($url ?? '', ['http', '#', 'mailto:', 'tel:']) ? $url : url($url ?: '/#contact');
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
                            <a href="{{ $href($page['hero']['button_url']) }}" class="yg-btn yg-btn--gold">{{ $page['hero']['button_text'] }} <span aria-hidden="true">→</span></a>
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

    @include('layouts.footer')
</body>
</html>
