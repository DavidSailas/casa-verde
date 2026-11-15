<!DOCTYPE html>
<html lang="en">

<head>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261017">
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    @include('layouts.seo', ['seoPage' => 'wellness', 'seoTitle' => 'Wellness'])

    @vite([
        'resources/css/header.css',
        'resources/css/wellness.css',
        'resources/css/theme.css',
        'resources/css/footer.css','resources/css/rule.css', 'resources/css/responsive.css',
        'resources/js/app.js'
    ])

    <style>
/* =========================================================
   HERO – photo blended into the text, kept inside the container
   (scoped to .wellness-page so it wins over theme.css)
========================================================= */

.wellness-page .wellness-hero {
    position: relative;
    display: block;
    max-width: 1440px;
    margin: 0 auto;
    min-height: 365px;
    padding-top: 96px;
    overflow: hidden;
    isolation: isolate;
    background: var(--cream);
}

.wellness-page .wellness-hero-image {
    position: absolute;
    top: 96px;
    right: 0;
    bottom: 0;
    z-index: 0;
    width: 64%;
    height: auto;
    overflow: hidden;
    border-radius: 0;
    box-shadow: none;
    -webkit-mask-image: linear-gradient(to right, transparent 0, rgba(0, 0, 0, .5) 18%, #000 46%);
            mask-image: linear-gradient(to right, transparent 0, rgba(0, 0, 0, .5) 18%, #000 46%);
}

.wellness-page .wellness-hero-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: 60% 40%;
}

.wellness-page .wellness-introduction {
    position: relative;
    z-index: 1;
    width: min(56%, 640px);
    min-height: 269px;
    padding: 24px 30px 24px clamp(24px, 3.4vw, 47px);
}

.wellness-page .wellness-label {
    font-family: var(--cv-serif, serif);
    font-size: 1.1rem;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.wellness-page .wellness-introduction h1 {
    font-family: var(--cv-serif, serif);
    font-size: clamp(2rem, 3.2vw, 3rem);
    font-weight: 600;
    line-height: 1.04;
    color: var(--cv-forest, #06211b);
}

.wellness-page .wellness-description {
    font-family: var(--cv-serif, serif);
    font-size: 1.15rem;
    line-height: 1.45;
    max-width: 430px;
    color: #31443f;
}

/* Cards + feature strip: serif type like the design */
.wellness-page .wellness-card-content h2,
.wellness-page .feature-item h3 {
    font-family: var(--cv-serif, serif);
    font-size: 1.05rem;
    font-weight: 700;
}

.wellness-page .wellness-card-content p,
.wellness-page .feature-item p {
    font-family: var(--cv-serif, serif);
    font-size: .98rem;
    line-height: 1.35;
}

.wellness-page .spa-menu-button {
    font-family: var(--cv-serif, serif);
    font-size: 1rem;
    letter-spacing: .04em;
}

@media (max-width: 1100px) {
    .wellness-page .wellness-introduction { width: min(60%, 640px); }
    .wellness-page .wellness-hero-image { width: 60%; }
}

@media (max-width: 768px) {
    .wellness-page .wellness-hero { min-height: 0; padding-top: 76px; }
    .wellness-page .wellness-introduction { width: 100%; min-height: 0; padding: 28px 24px 150px; }
    .wellness-page .wellness-hero-image {
        top: auto;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 320px;
        -webkit-mask-image: linear-gradient(to bottom, transparent 0, rgba(0, 0, 0, .55) 14%, #000 42%);
                mask-image: linear-gradient(to bottom, transparent 0, rgba(0, 0, 0, .55) 14%, #000 42%);
    }
}


/* Line icons (replace the text glyphs) */
.wellness-page .feature-icon svg { stroke-width: 1.2; }


/* =========================================================
   TREATMENT CARDS – round icon badge on each photo
   (inline on purpose: it works without rebuilding the CSS)
   The photo keeps its own shape, so the badge always sits
   in the same spot, on every screen size.
========================================================= */

.wellness-page .wellness-card-image {
    height: auto;
    aspect-ratio: 236 / 130;
    container-type: inline-size;
}

.wellness-page .wellness-card-badge {
    position: absolute;
    top: -.5cqw;
    left: -.5cqw;
    z-index: 2;
    width: calc(var(--wb, 17) * 1.1765cqw);
    height: calc(var(--wb, 17) * 1.1765cqw);
    max-width: 120px;
    max-height: 120px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: #0b3b31;
    border: 1.5px solid #c9a06a;
    color: #e9cfa4;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .35);
    pointer-events: none;
}

.wellness-page .wellness-card-badge .cv-icon,
.wellness-page .wellness-card-badge svg,
.wellness-page .wellness-card-badge img {
    width: 94% !important;
    height: 94% !important;
    object-fit: contain;
}

.wellness-page .wellness-card-badge .cv-emoji {
    font-size: clamp(1.8rem, 6.5cqw, 3.4rem);
    line-height: 1;
}
    </style>

    <style>
        /* Wellness page: refined styling on top of the rules above. Text, icons and layout are unchanged. */
        .wellness-page {
            --wl-forest: #06211b;
            --wl-gold: #c9a06a;
            --wl-line: rgba(201, 160, 106, .55);
            background: #f7f2ea;
        }

        /* Hero: full width, photo reaches the right edge and fades into the cream text panel */
        .wellness-page .wellness-hero { max-width: none; margin: 0; min-height: 400px; padding-top: 96px; background: #f7f2ea; }
        .wellness-page .wellness-hero::after {
            content: "";
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 1px;
            z-index: 2;
            background: linear-gradient(90deg, transparent, var(--wl-line) 20%, var(--wl-line) 80%, transparent);
        }
        .wellness-page .wellness-hero-image {
            width: 66%;
            -webkit-mask-image: linear-gradient(to right, transparent 0, rgba(0, 0, 0, .55) 16%, #000 40%);
                    mask-image: linear-gradient(to right, transparent 0, rgba(0, 0, 0, .55) 16%, #000 40%);
        }
        .wellness-page .wellness-hero-image img { object-position: 62% 40%; }
        .wellness-page .wellness-introduction { width: min(52%, 640px); min-height: 300px; padding: 40px 30px 40px clamp(28px, 6vw, 110px); display: flex; flex-direction: column; justify-content: center; }
        .wellness-page .wellness-label { margin: 0 0 14px; font-size: .95rem; font-weight: 500; letter-spacing: .32em; color: #b98b55; }
        .wellness-page .wellness-label::after { content: ""; display: block; width: 54px; height: 1px; margin-top: 10px; background: var(--wl-gold); }
        .wellness-page .wellness-introduction h1 { font-size: clamp(2.2rem, 3.5vw, 3.3rem); font-weight: 600; line-height: 1.06; color: var(--wl-forest); }
        .wellness-page .wellness-description { margin-top: 22px; max-width: 440px; font-size: 1.2rem; line-height: 1.55; color: #31443f; }

        /* Treatment cards */
        .wellness-page .wellness-services { gap: 18px; padding: 30px 28px 26px; }
        .wellness-page .wellness-card-image {
            border-radius: 20px;
            box-shadow: 0 18px 30px -20px rgba(6, 33, 27, .7), 0 0 0 1px rgba(201, 160, 106, .35);
        }
        .wellness-page .wellness-card { transition: transform .35s ease; }
        .wellness-page .wellness-card:hover { transform: translateY(-4px); }
        .wellness-page .wellness-card-content { padding: 18px 10px 0; }
        .wellness-page .wellness-card-content h2 { font-size: 1.15rem; letter-spacing: .01em; color: var(--wl-forest); margin-bottom: 6px; }
        .wellness-page .wellness-card-content p { font-size: 1.02rem; line-height: 1.4; color: #4a5852; }

        /* Feature strip */
        .wellness-page .wellness-features { margin: 0 28px; padding: 26px 0 36px; border-top: 0; position: relative; gap: 24px; }
        .wellness-page .wellness-features::before {
            content: "";
            position: absolute;
            left: 0; right: 0; top: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--wl-line) 15%, var(--wl-line) 85%, transparent);
        }
        .wellness-page .feature-icon { width: 56px; height: 56px; color: #c98f4f; }
        .wellness-page .feature-item h3 { font-size: 1.12rem; color: var(--wl-forest); }
        .wellness-page .feature-item p { font-size: 1.02rem; line-height: 1.4; color: #4a5852; }
        .wellness-page .spa-menu-button {
            min-height: 56px;
            padding: 0 34px;
            background: var(--wl-forest);
            color: #e6c28f;
            border: 1.5px solid var(--wl-forest);
            font-weight: 700;
            letter-spacing: .12em;
            box-shadow: 0 16px 26px -16px rgba(6, 33, 27, .85);
        }
        .wellness-page .spa-menu-button:hover { background: var(--wl-gold); border-color: var(--wl-gold); color: var(--wl-forest); }
        .wellness-page .spa-menu-button:focus-visible { outline: 2px solid var(--wl-gold); outline-offset: 4px; }

        @media (max-width: 1100px) {
            .wellness-page .wellness-introduction { width: min(58%, 640px); padding-left: clamp(24px, 5vw, 60px); }
            .wellness-page .wellness-hero-image { width: 62%; }
        }
        @media (max-width: 768px) {
            .wellness-page .wellness-hero { min-height: 0; padding-top: 76px; }
            .wellness-page .wellness-introduction { width: 100%; min-height: 0; padding: 32px 24px 170px; }
            .wellness-page .wellness-hero-image { width: 100%; }
            .wellness-page .wellness-services { grid-template-columns: 1fr 1fr; gap: 22px 14px; padding: 26px 18px; }
            .wellness-page .wellness-features { margin: 0 18px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .wellness-page *, .wellness-page *::before, .wellness-page *::after { transition: none !important; }
        }
    </style>

</head>

<body>

@include('layouts.header')

    @php
        $pc = fn (string $section) => \App\Models\HomeContent::pageRows($contents, $section);
        $img = fn (?string $path, string $fallback = 'images/main.jpg') => asset($path && file_exists(public_path($path)) ? $path : $fallback);
        $href = fn (?string $url) => \Illuminate\Support\Str::startsWith($url ?? '', ['http', '#', 'mailto:', 'tel:']) ? $url : url($url ?: '/#contact');
        $lines = fn (?string $text) => array_pad(explode("\n", str_replace("\r", '', trim((string) $text)), 2), 2, '');
        $hero = $pc('wellness_page_hero')->first();
        $perks = $pc('wellness_page_perks');
        $menu = $pc('wellness_page_menu')->first();
    @endphp

<main class="wellness-page">

    {{-- Hero --}}
    <section class="wellness-hero">
        <div class="wellness-introduction">
            <p class="wellness-label">{{ $hero?->subtitle }}</p>
            <h1>{!! nl2br(e($hero?->title)) !!}</h1>
            <p class="wellness-description">{{ $hero?->description }}</p>
        </div>

        <div class="wellness-hero-image">
            <img src="{{ $img($hero?->image, 'images/wellness1.jpg') }}" alt="{{ trim(strtok((string) $hero?->title, "\n")) }}">
        </div>
    </section>

    {{-- Treatments --}}
    <section class="wellness-services">
        @foreach($pc('wellness_page_treatments') as $treatment)
            <article class="wellness-card">
                <div class="wellness-card-image">
                    <img src="{{ $img($treatment->image, 'images/wellness2.jpg') }}" alt="{{ $treatment->title }}">

                    @if(filled($treatment->icon))
                        @php
                            // "Badge size" from the admin: 17 when empty, never below 8 or above 30
                            $badgeSize = is_numeric($treatment->price) && (float) $treatment->price > 0
                                ? max(8, min(30, (int) round((float) $treatment->price)))
                                : 17;
                        @endphp
                        <span class="wellness-card-badge" style="--wb: {{ $badgeSize }}" aria-hidden="true">
                            <x-icon :name="$treatment->icon" :size="26" />
                        </span>
                    @endif
                </div>
                <div class="wellness-card-content">
                    <h2>{{ $treatment->title }}</h2>
                    <p>{{ $treatment->description }}</p>
                </div>
            </article>
        @endforeach
    </section>

    {{-- Highlights and spa menu button --}}
    <section class="wellness-features">
        @foreach($perks as $item)
            @if($loop->index === 2 && $menu && filled($menu->button_text))
                <div class="feature-action">
                    <a href="{{ $href($menu->button_url ?: '#') }}" class="spa-menu-button">{{ $menu->button_text }}</a>
                </div>
            @endif

            <div class="feature-item">
                @if(filled($item->icon))
                    <div class="feature-icon">
                        <x-icon :name="$item->icon" :size="48" />
                    </div>
                @endif
                <div>
                    <h3>{{ $item->title }}</h3>
                    <p>{!! nl2br(e($item->description)) !!}</p>
                </div>
            </div>
        @endforeach

        @if($perks->count() <= 2 && $menu && filled($menu->button_text))
            <div class="feature-action">
                <a href="{{ $href($menu->button_url ?: '#') }}" class="spa-menu-button">{{ $menu->button_text }}</a>
            </div>
        @endif
    </section>

</main>

    @include('layouts.footer')

</body>

</html>