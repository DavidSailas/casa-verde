@php
    $cvVer = fn ($p) => file_exists(public_path($p)) ? filemtime(public_path($p)) : 1;
    // One logo, edited in the admin (Site-wide): header, button badges, footer.
    $cvLogo = \App\Models\HomeContent::siteLogo();
@endphp
<style>:root { --cv-logo: url('{{ asset($cvLogo) }}?v={{ $cvVer($cvLogo) }}'); }</style>

@php
    $routeFor = [
        'villas'      => 'villas.index',
        'experiences' => 'experiences.index',
        'wellness'    => 'wellness.index',
        'dining'      => 'dining.index',
        'long stay'   => 'longstay.index',
        'reviews'     => 'reviews.index',
        'yoga reset'  => 'yoga.index',
        'contact'     => 'contact.index',
        'about'       => 'about.index',
    ];

    // Menu items that share one dropdown. Each stays its own row in the admin (Site-wide > Navigation: rename,
    // reorder, switch on or off); the dropdown appears where the first of them is, and with fewer than two
    // switched-on items they are shown as plain links.
    $navGroup = ['label' => 'DISCOVER', 'slugs' => ['reviews', 'journal']];

    $resolveNav = function ($nav) use ($routeFor) {
        $slug = strtolower(trim($nav->title));

        if (isset($routeFor[$slug])) {
            return [
                route($routeFor[$slug]),
                request()->routeIs($routeFor[$slug], str_replace('.index', '.*', $routeFor[$slug])),
            ];
        }

        // Section anchors (#about, #contact...) live on the home page, so they must work from every other page too.
        $href = str_starts_with((string) $nav->button_url, '#') ? url('/') . $nav->button_url : (string) $nav->button_url;
        $path = trim((string) parse_url($href, PHP_URL_PATH), '/');

        return [$href, $path !== '' && request()->is($path, $path.'/*')];
    };

    $navRows = $contents->get('navigation', collect())->values();
    $groupRows = $navRows->filter(fn ($nav) => in_array(strtolower(trim($nav->title)), $navGroup['slugs'], true))->values();
    $groupFirst = $groupRows->count() >= 2 ? $groupRows->first()->id : null;

    // Pages that open from a dropdown under a menu item (the item itself still has its own page).
    $subNav = [];

    // The link text comes from the admin (Winterresidenz > Heading > "Menu link text"); null = page switched off.
    $winterMenu = \App\Support\WinterResidence::menuLabel();

    if ($winterMenu !== null) {
        $subNav['long stay'] = [
            ['title' => 'Long Stay', 'href' => route('longstay.index'), 'active' => request()->routeIs('longstay.*')],
            ['title' => $winterMenu, 'href' => route('winter.index'), 'active' => request()->routeIs('winter.*')],
        ];
    }
@endphp

<header class="navbar">

    <a href="{{ url('/') }}" class="logo-area" translate="no" aria-label="Casa Verde Cliff Resort & Spa – home">

        @if($site)
            <img src="{{ asset($cvLogo) }}?v={{ $cvVer($cvLogo) }}" alt="" width="42" height="42">

            <span class="logo-text">
                <span>{{ $site->title }}</span>
                <small>{{ $site->subtitle }}</small>
            </span>
        @endif

    </a>

    <button
        type="button"
        class="nav-toggle"
        aria-controls="primary-nav"
        aria-expanded="false"
        aria-label="Open menu"
    >
        <span></span>
    </button>

    <nav id="primary-nav" class="nav-links" aria-label="Main">

        @foreach($navRows as $nav)

            @if($groupFirst !== null && in_array(strtolower(trim($nav->title)), $navGroup['slugs'], true))
                @if($nav->id === $groupFirst)
                    @php
                        $groupItems = $groupRows->map(function ($row) use ($resolveNav) {
                            [$gHref, $gActive] = $resolveNav($row);

                            return ['title' => $row->title, 'href' => $gHref, 'active' => $gActive];
                        });
                        $groupActive = $groupItems->contains('active', true);
                    @endphp
                    <div class="nav-item nav-item--sub" data-nav-sub>
                        <a href="{{ $groupItems->first()['href'] }}" @class(['is-active' => $groupActive]) @if($groupActive) aria-current="page" @endif>{{ \Illuminate\Support\Str::upper($navGroup['label']) }}</a>
                        <button type="button" class="nav-sub-toggle" aria-expanded="false" aria-controls="nav-sub-discover" aria-label="Show {{ $navGroup['label'] }} pages" data-nav-sub-toggle><span aria-hidden="true"></span></button>
                        <div class="nav-sub" id="nav-sub-discover">
                            @foreach($groupItems as $sub)
                                <a href="{{ $sub['href'] }}" @class(['is-active' => $sub['active']]) @if($sub['active']) aria-current="page" @endif>{{ $sub['title'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                @continue
            @endif

            @php
                $slug = strtolower(trim($nav->title));
                [$href, $active] = $resolveNav($nav);
            @endphp

            @if(isset($subNav[$slug]))
                @php $subActive = collect($subNav[$slug])->contains('active', true); @endphp
                <div class="nav-item nav-item--sub" data-nav-sub>
                    <a
                        href="{{ $href }}"
                        @class(['is-active' => $subActive])
                        @if($active) aria-current="page" @endif
                    >
                        {{ $nav->title }}
                    </a>
                    <button type="button" class="nav-sub-toggle" aria-expanded="false" aria-controls="nav-sub-{{ \Illuminate\Support\Str::slug($slug) }}" aria-label="Show {{ $nav->title }} pages" data-nav-sub-toggle><span aria-hidden="true"></span></button>
                    <div class="nav-sub" id="nav-sub-{{ \Illuminate\Support\Str::slug($slug) }}">
                        @foreach($subNav[$slug] as $sub)
                            <a href="{{ $sub['href'] }}" @class(['is-active' => $sub['active']]) @if($sub['active']) aria-current="page" @endif>{{ $loop->first ? $nav->title : $sub['title'] }}</a>
                        @endforeach
                    </div>
                </div>
            @else
                <a
                    href="{{ $href }}"
                    @class(['is-active' => $active])
                    @if($active) aria-current="page" @endif
                >
                    {{ $nav->title }}
                </a>
            @endif

        @endforeach

        {{-- Shown inside the mobile menu only --}}
        <a href="{{ route('booking.index') }}" class="book-button nav-book-mobile">
            <span class="book-icon" aria-hidden="true"><img src="{{ asset('images/logo-badge.png') }}" alt="" class="cv-booking-logo"></span>
            Book your stay
        </a>

    </nav>

    @include('layouts.language')

    <a href="{{ route('booking.index') }}" class="book-button nav-book-desktop">
        <span class="book-icon" aria-hidden="true"><img src="{{ asset('images/logo-badge.png') }}" alt="" class="cv-booking-logo"></span>
        Book your stay
    </a>

</header>
<script>window.cvFitNav && window.cvFitNav();</script>
<style>
/* A short, even fade between pages (browsers without cross-page transitions just load the page as usual).
   The header is deliberately NOT given its own transition layer: an open dropdown makes that layer a different
   size from the one on the next page, and the header visibly jumped. */
@view-transition { navigation: auto; }
::view-transition-group(root) { animation-duration: .2s; }
@media (prefers-reduced-motion: reduce) { @view-transition { navigation: none; } }

/* The moment a menu link is clicked, any open dropdown closes at once, so it cannot hang open (or reopen under
   the mouse) while the next page loads. */
.navbar.is-leaving .nav-sub { opacity: 0 !important; visibility: hidden !important; pointer-events: none; transition: none !important; }
</style>
<script>
(function () {
    var bar = document.querySelector('.navbar');
    if (!bar) return;

    var timer = null;

    bar.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a[href]') : null;

        // Only a click that really opens another page: not a new tab, not a link to a place on this same page.
        if (!link || e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (link.target && link.target !== '_self') return;
        if (link.origin !== location.origin || (link.pathname === location.pathname && link.search === location.search)) return;

        bar.classList.add('is-leaving');
        clearTimeout(timer);
        timer = setTimeout(function () { bar.classList.remove('is-leaving'); }, 1500);   // in case the visit is cancelled
    });

    // Coming back with the browser's Back button shows the page as it was left: open the dropdowns again.
    window.addEventListener('pageshow', function () { bar.classList.remove('is-leaving'); });
})();
</script>
<div id="cv-currency-control" class="cv-currency-control notranslate" translate="no" aria-label="Display currency">
  <label for="cv-currency-select">Currency</label>
  <select id="cv-currency-select" aria-label="Choose display currency">
    @foreach(['PHP','USD','EUR','GBP','AUD','CAD','JPY','SGD','KRW','CNY','HKD','AED','INR','NZD'] as $code)
      <option value="{{ $code }}">{{ $code }}</option>
    @endforeach
  </select>
</div>
<style>
.cv-currency-control{position:fixed;bottom:18px;right:18px;z-index:900;background:#fff;color:#18372b;box-shadow:0 3px 20px #0002;border:1px solid #d7ded8;border-radius:12px;padding:9px 12px;display:flex;align-items:center;gap:9px;font:600 12px system-ui,sans-serif}
.cv-currency-control select{background:transparent;color:inherit;border:0;font:600 13px system-ui,sans-serif;cursor:pointer;max-width:85px}
@media(max-width:600px){.cv-currency-control{bottom:10px;right:10px;padding:7px 9px}}
/* The floating currency box sat behind the chat button: hidden. The selector stays in the page so prices still convert to the visitor's currency. */
.cv-currency-control{display:none !important}
/* Site scrollbar: slim forest-green track with a gold thumb, matching the header and footer. */
html{scrollbar-width:auto;scrollbar-color:#c9a06a #06211b}
html::-webkit-scrollbar{width:12px;height:12px}
html::-webkit-scrollbar-track{background:#06211b}
html::-webkit-scrollbar-thumb{background:#c9a06a;border:3px solid #06211b;border-radius:999px;min-height:56px}
html::-webkit-scrollbar-thumb:hover{background:#dfb987}
html::-webkit-scrollbar-thumb:active{background:#b98b55}
html::-webkit-scrollbar-corner{background:#06211b}
@media(max-width:700px){html::-webkit-scrollbar{width:8px}html::-webkit-scrollbar-thumb{border-width:2px}}
</style>
<script defer src="{{ asset('js/casa-currency.js') }}?v=2" data-rates-url="{{ route('currency.rates') }}"></script>

{{-- Header polish: glass bar once scrolled, reading-progress line, refined links and Book button, animated mobile menu.
     Inline on purpose (like the language menu), so it works without a rebuild. Sizes and letter-spacing of the menu
     links are left alone: the language fitter (cvFitNav) measures them. --}}
<style id="cv-header-polish">
/* ---------- Bar: soft gradient, fading gold hairline, reading-progress line ---------- */
header.navbar {
    border-bottom: 0;
    background: linear-gradient(180deg, #05271f 0%, #04201a 100%);
    transition: height .35s cubic-bezier(.22, 1, .36, 1), background .35s ease, box-shadow .35s ease;
}
header.navbar::before {
    content: "";
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(216, 180, 130, .55) 18%, rgba(216, 180, 130, .55) 82%, transparent);
    pointer-events: none;
}
header.navbar::after {
    content: "";
    position: absolute;
    left: 0; bottom: 0;
    width: 100%; height: 2px;
    background: linear-gradient(90deg, #b98b55, #f0cc8e);
    transform-origin: left center;
    transform: scaleX(var(--cv-scroll, 0));
    opacity: 0;
    transition: opacity .3s ease;
    pointer-events: none;
}
header.navbar.is-scrolled::after { opacity: 1; }

/* Scrolled: frosted glass with a soft shadow */
header.navbar.is-scrolled {
    background: rgba(4, 32, 26, .86);
    -webkit-backdrop-filter: blur(14px) saturate(140%);
            backdrop-filter: blur(14px) saturate(140%);
    box-shadow: 0 14px 34px -16px rgba(0, 0, 0, .65);
}
/* ---------- Logo lockup: emblem, name, subtitle: centred, with room above and below ----------
   The bar keeps the same height when scrolled (it used to shrink and the logo spilled out of it). */
header.navbar .logo-area {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: clamp(4px, .38vw, 6px);
}
header.navbar .logo-area img {
    width: clamp(30px, 2.3vw, 40px);
    height: clamp(30px, 2.3vw, 40px);
    filter: drop-shadow(0 0 10px rgba(216, 180, 130, .28));
    transition: transform .4s cubic-bezier(.22, 1, .36, 1), filter .4s ease;
}
header.navbar .logo-area:hover img { transform: scale(1.07); filter: drop-shadow(0 0 14px rgba(240, 204, 142, .5)); }

header.navbar .logo-text { align-items: center; text-align: center; gap: clamp(3px, .3vw, 5px); }
header.navbar .logo-text span { color: #dcb887; letter-spacing: .16em; transition: color .3s ease; }
header.navbar .logo-area:hover .logo-text span { color: #f1cf9c; }

/* Subtitle set between two short gold hairlines */
header.navbar .logo-text small {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    color: #c4ae85;
    letter-spacing: .24em;
}
header.navbar .logo-text small::before,
header.navbar .logo-text small::after {
    content: "";
    display: block;
    flex: none;
    width: clamp(10px, 1.1vw, 20px);
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(216, 180, 130, .75));
}
header.navbar .logo-text small::after { transform: scaleX(-1); }
@media (max-width: 560px) { header.navbar .logo-text small { display: none; } }

/* ---------- Menu links: gold gradient underline that grows from the centre ---------- */
.nav-links a:not(.book-button)::after {
    left: 0; right: 0; bottom: 0;
    height: 2px;
    border-radius: 2px;
    background: linear-gradient(90deg, #b98b55, #f0cc8e, #b98b55);
    transform-origin: center;
    transition: transform .35s cubic-bezier(.22, 1, .36, 1);
}
.nav-links a:not(.book-button):focus-visible { outline: 2px solid #f1cf9c; outline-offset: 6px; border-radius: 4px; }

/* Dropdown panels: a touch deeper and softer */
.nav-links .nav-sub {
    background: rgba(5, 30, 25, .98);
    box-shadow: 0 28px 52px -18px rgba(0, 0, 0, .7), 0 0 0 1px rgba(216, 180, 130, .08);
}
.cv-lang__btn { transition: background .25s, color .25s, border-color .25s, box-shadow .25s; }
.cv-lang__btn:hover, .cv-lang__btn[aria-expanded="true"] { box-shadow: 0 0 0 3px rgba(216, 180, 130, .12); }

/* ---------- Book your stay: warm gradient, glow and a light sweep on hover ---------- */
header.navbar .book-button {
    position: relative;
    overflow: hidden;
    background: linear-gradient(180deg, #e8b878 0%, #d49b5c 100%);
    box-shadow: 0 10px 24px -12px rgba(217, 163, 102, .8), inset 0 1px 0 rgba(255, 255, 255, .4);
    transition: transform .3s cubic-bezier(.22, 1, .36, 1), box-shadow .3s ease, filter .3s ease;
}
header.navbar .book-button::before {
    content: "";
    position: absolute;
    top: 0; bottom: 0;
    left: -70%;
    width: 40%;
    background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .55), transparent);
    transform: skewX(-20deg);
    transition: left .7s cubic-bezier(.22, 1, .36, 1);
    pointer-events: none;
}
header.navbar .book-button:hover,
header.navbar .book-button:focus-visible {
    background: linear-gradient(180deg, #efc283 0%, #dca565 100%);
    transform: translateY(-1px);
    box-shadow: 0 16px 30px -12px rgba(217, 163, 102, .95), inset 0 1px 0 rgba(255, 255, 255, .5);
    outline: none;
}
header.navbar .book-button:hover::before,
header.navbar .book-button:focus-visible::before { left: 130%; }
header.navbar .book-button:focus-visible { box-shadow: 0 0 0 3px #04201a, 0 0 0 5px #f0cc8e; }
header.navbar .book-button:active { transform: translateY(0); filter: brightness(.96); }

/* ---------- Phone / tablet menu ---------- */
@media (max-width: 1180px) {
    header.navbar .nav-toggle { transition: background .25s, border-color .25s; }
    header.navbar .nav-toggle:hover,
    header.navbar.nav-open .nav-toggle { background: rgba(216, 180, 130, .14); border-color: rgba(216, 180, 130, .8); }

    header.navbar .nav-links {
        background: rgba(4, 32, 26, .97);
        -webkit-backdrop-filter: blur(14px);
                backdrop-filter: blur(14px);
        border-bottom: 0;
        box-shadow: 0 34px 50px -22px rgba(0, 0, 0, .7);
    }
    .nav-open .nav-links { animation: cv-menu-in .35s cubic-bezier(.22, 1, .36, 1); }
    .nav-open .nav-links > a,
    .nav-open .nav-links > .nav-item { animation: cv-link-in .5s cubic-bezier(.22, 1, .36, 1) both; }
    .nav-open .nav-links > :nth-child(1) { animation-delay: .04s; }
    .nav-open .nav-links > :nth-child(2) { animation-delay: .08s; }
    .nav-open .nav-links > :nth-child(3) { animation-delay: .12s; }
    .nav-open .nav-links > :nth-child(4) { animation-delay: .16s; }
    .nav-open .nav-links > :nth-child(5) { animation-delay: .20s; }
    .nav-open .nav-links > :nth-child(6) { animation-delay: .24s; }
    .nav-open .nav-links > :nth-child(n+7) { animation-delay: .28s; }

    /* The active page is easy to spot: gold text and a gold bar on the left */
    .nav-links a.is-active:not(.book-button) { color: #f1cf9c; box-shadow: inset 2px 0 0 #d7a468; padding-left: 14px; }

    /* Full-width Book button at the bottom of the menu: easy to reach with a thumb */
    header.navbar .nav-links .nav-book-mobile {
        align-self: stretch;
        justify-content: center;
        margin: 22px 0 0;
        min-height: 50px;
        font-size: .8rem;
        letter-spacing: .1em;
        text-transform: uppercase;
    }
}
@keyframes cv-menu-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }
@keyframes cv-link-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

@media (prefers-reduced-motion: reduce) {
    header.navbar, header.navbar *, header.navbar::after { animation: none !important; transition: none !important; }
}
</style>
<script>
/* Header polish: reading-progress line and the scrolled look (app.js sets the same class; this keeps it working on its own). */
(function () {
    var bar = document.querySelector('.navbar');
    if (!bar) return;

    var queued = false;

    function update() {
        queued = false;
        var max = document.documentElement.scrollHeight - window.innerHeight;
        var progress = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
        bar.style.setProperty('--cv-scroll', progress.toFixed(4));
        bar.classList.toggle('is-scrolled', window.scrollY > 8);
    }

    function queue() {
        if (queued) return;
        queued = true;
        requestAnimationFrame(update);
    }

    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue);
    window.addEventListener('pageshow', queue);
    update();
})();
</script>
