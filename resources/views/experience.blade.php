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

    @include('layouts.seo', ['seoPage' => 'experiences', 'seoTitle' => 'Experiences'])

    @vite([
        'resources/css/header.css',
        'resources/css/experience.css',
        'resources/css/theme.css',
        'resources/css/footer.css','resources/css/rule.css', 'resources/css/responsive.css',
        'resources/js/app.js'
    ])

    <style>
        /* Experiences page – matched to the design (inline so it shows without a rebuild;
           can be moved into resources/css/experience.css) */

        /* Hero */
        .experience-page .experience-hero {
            grid-template-columns: 38% minmax(0, 1fr);
            gap: 24px;
            padding: 96px 44px 18px;
            align-items: center;
        }
        .experience-page .hero-introduction { padding-left: 22px; }
        .experience-page .hero-label {
            font-family: var(--cv-sans, sans-serif);
            font-size: 1.05rem;
            letter-spacing: .3em;
            color: #dfa562;
            margin: 0 0 10px;
        }
        .experience-page .hero-introduction h1 {
            font-family: var(--cv-serif, serif);
            font-size: clamp(2rem, 3.1vw, 2.9rem);
            font-weight: 500;
            line-height: 1.12;
            color: #10231f;
            white-space: nowrap;
        }
        /* theme.css styles .hero-description for the dark home hero (cream text + shadow);
           reset it here so it reads on the cream background */
        .experience-page .hero-description {
            font-family: var(--cv-sans, sans-serif);
            font-size: 1.02rem;
            line-height: 1.5;
            max-width: 420px;
            margin-top: 18px;
            color: #1d2b27;
            text-shadow: none;
        }
        .experience-page .hero-image { height: 195px; border-radius: 18px; }

        /* Section headings */
        .experience-page .experiences-area { padding: 0 44px 14px; }
        .experience-page .section-heading { gap: 24px; margin: 0 0 16px; }
        .experience-page .section-heading h2 {
            font-family: var(--cv-serif, serif);
            font-size: 1.3rem;
            letter-spacing: .04em;
            color: #dfa562;
        }

        /* Signature cards */
        .experience-page .signature-grid { gap: 50px; }
        .experience-page .signature-card { border: 0; background: transparent; overflow: visible; }
        .experience-page .signature-image { height: 96px; border-radius: 16px 16px 0 0; }
        .experience-page .signature-title {
            height: 40px;
            border: 1px solid #e3b078;
            font-family: var(--cv-serif, serif);
            font-size: 1.45rem;
            color: #10231f;
        }

        /* More experiences */
        .experience-page .more-experiences { padding-bottom: 18px; }
        .experience-page .more-grid { gap: 22px; align-items: stretch; }
        .experience-page .more-card { border: 0; background: transparent; overflow: visible; display: flex; flex-direction: column; }
        .experience-page .more-image { flex: none; }
        .experience-page .more-image { height: 94px; border-radius: 16px 16px 0 0; }
        .experience-page .more-content {
            border: 1px solid #e3b078;
            padding: 10px 4px 10px;
            flex: 1;
        }
        .experience-page .more-content h3 {
            font-family: var(--cv-serif, serif);
            font-size: 1.15rem;
            font-weight: 700;
            color: #10231f;
            margin-bottom: 4px;
            white-space: nowrap;
        }
        .experience-page .more-content p {
            font-family: var(--cv-serif, serif);
            font-size: 1rem;
            color: #3a4540;
        }

        /* Concierge bar */
        .experience-page .concierge-bar { min-height: 78px; padding: 8px 42px; gap: 26px; background: #06211b; }
        .experience-page .concierge-item { gap: 16px; border-right: 1px solid #c58f55; min-width: 0; }
        .experience-page .concierge-item p br { display: none; }
        .experience-page .concierge-icon { width: 52px; height: 52px; border: 0; color: #f0e6d6; }
        .experience-page .concierge-item:first-child .concierge-icon { color: #e0a366; }
        .experience-page .concierge-icon svg { stroke-width: 1.2; }
        .experience-page .concierge-item h3 {
            font-family: var(--cv-serif, serif);
            font-size: 1.25rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 3px;
            white-space: nowrap;
        }
        .experience-page .concierge-item p {
            font-family: var(--cv-serif, serif);
            font-size: .98rem;
            line-height: 1.25;
            color: #e8e0d2;
        }
        .experience-page .plan-button {
            gap: 8px;
            padding: 6px 28px 6px 6px;
            border-radius: 40px;
            background: #dca265;
            font-family: var(--cv-sans, sans-serif);
            font-size: 1rem;
            letter-spacing: .02em;
        }
        .experience-page .plan-button .plan-logo {
            width: 48px; height: 48px;
            border: 0;
            background: #06211b;
            overflow: hidden;
        }
        .experience-page .plan-logo img { width: 100%; height: 100%; object-fit: cover; }

        @media (max-width: 1100px) {
            .experience-page .more-grid { grid-template-columns: repeat(4, 1fr); }
            .experience-page .hero-introduction h1 { white-space: normal; }
            .experience-page .concierge-item { border-right: 0; }
            .experience-page .more-content h3,
            .experience-page .concierge-item h3 { white-space: normal; }
        }
        @media (max-width: 768px) {
            .experience-page .experience-hero { grid-template-columns: 1fr; padding: 96px 20px 20px; }
            .experience-page .hero-introduction { padding-left: 0; }
            .experience-page .experiences-area { padding: 0 20px 20px; }
            .experience-page .signature-grid { grid-template-columns: 1fr; gap: 18px; }
            .experience-page .signature-image { height: 160px; }
            .experience-page .more-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .experience-page .more-image { height: 110px; }
            .experience-page .concierge-bar { flex-direction: column; align-items: stretch; padding: 20px; }
            .experience-page .concierge-item { border-bottom: 1px solid rgba(197,143,85,.6); padding: 10px 0 14px; }
            .experience-page .plan-button { justify-content: center; }
        }
    </style>

    <style>
        /* Experiences page: refined styling on top of the rules above. Text, icons and layout are unchanged. */
        .experience-page {
            --ex-forest: #06211b;
            --ex-gold: #c9a06a;
            --ex-line: rgba(201, 160, 106, .55);
            background: #f7f2ea;
        }

        /* Hero */
        .experience-page .experience-hero { padding: 118px 44px 26px; gap: 40px; }
        .experience-page .hero-label { font-size: .95rem; font-weight: 500; letter-spacing: .32em; color: #b98b55; margin: 0 0 14px; }
        .experience-page .hero-introduction h1 { font-size: clamp(2.1rem, 3.3vw, 3.1rem); font-weight: 600; letter-spacing: .01em; line-height: 1.1; color: var(--ex-forest); }
        .experience-page .hero-description { font-family: var(--cv-serif, serif); font-size: 1.2rem; line-height: 1.55; max-width: 430px; margin-top: 20px; color: #31443f; }
        .experience-page .hero-image {
            height: 230px;
            border-radius: 22px;
            box-shadow: 0 26px 40px -26px rgba(6, 33, 27, .7), 0 0 0 1px rgba(201, 160, 106, .35);
        }
        .experience-page .hero-image img { transition: transform 1.6s cubic-bezier(.2, .6, .2, 1); }
        .experience-page .hero-image:hover img { transform: scale(1.03); }

        /* Section headings */
        .experience-page .experiences-area { padding: 6px 44px 22px; }
        .experience-page .section-heading { gap: 28px; margin: 0 0 22px; }
        .experience-page .section-heading span { background: linear-gradient(90deg, transparent, var(--ex-line) 35%, var(--ex-line)); }
        .experience-page .section-heading span:last-child { background: linear-gradient(270deg, transparent, var(--ex-line) 35%, var(--ex-line)); }
        .experience-page .section-heading h2 { font-size: 1.2rem; font-weight: 500; letter-spacing: .26em; color: #b98b55; }

        /* Cards: one rounded card (photo + caption) instead of a photo plus a separate box */
        .experience-page .signature-card,
        .experience-page .more-card {
            border: 1px solid var(--ex-line);
            border-radius: 18px;
            overflow: hidden;
            background: #fffaf2;
            box-shadow: 0 14px 26px -20px rgba(6, 33, 27, .6);
            transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
        }
        .experience-page .signature-card:hover,
        .experience-page .more-card:hover {
            transform: translateY(-4px);
            border-color: var(--ex-gold);
            box-shadow: 0 22px 30px -20px rgba(6, 33, 27, .65);
        }
        .experience-page .signature-grid { gap: 36px; }
        .experience-page .signature-image { height: 124px; border-radius: 0; }
        .experience-page .signature-title { height: 52px; border: 0; border-top: 1px solid var(--ex-line); font-size: 1.5rem; font-weight: 500; letter-spacing: .01em; color: var(--ex-forest); background: #fffaf2; }

        .experience-page .more-grid { gap: 18px; }
        .experience-page .more-image { height: 108px; border-radius: 0; }
        .experience-page .more-content { border: 0; border-top: 1px solid var(--ex-line); padding: 12px 6px 14px; background: #fffaf2; }
        .experience-page .more-content h3 { font-size: 1.15rem; color: var(--ex-forest); }
        .experience-page .more-content p { font-size: 1rem; line-height: 1.3; color: #4a5852; }

        /* Concierge bar */
        .experience-page .concierge-bar {
            position: relative;
            background: linear-gradient(180deg, #082a23, #06211b);
            border-top: 1px solid rgba(201, 160, 106, .5);
        }
        .experience-page .concierge-item { border-right-color: rgba(201, 160, 106, .45); }
        .experience-page .concierge-item h3 { color: #f4e3c4; }
        .experience-page .plan-button {
            background: linear-gradient(135deg, #e6bb86, #d19a5b);
            box-shadow: 0 14px 22px -14px rgba(0, 0, 0, .8);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .experience-page .plan-button:hover { transform: translateY(-2px); box-shadow: 0 18px 24px -14px rgba(0, 0, 0, .85); }
        .experience-page a:focus-visible { outline: 2px solid var(--ex-gold); outline-offset: 3px; }

        @media (max-width: 1100px) {
            .experience-page .experience-hero { padding-top: 108px; }
        }
        @media (max-width: 768px) {
            .experience-page .experience-hero { padding: 100px 20px 20px; gap: 22px; }
            .experience-page .hero-image { height: 200px; }
            .experience-page .signature-image { height: 170px; }
            .experience-page .more-image { height: 120px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .experience-page *, .experience-page *::before, .experience-page *::after { transition: none !important; }
        }
    </style>

</head>

<body data-no-photo-viewer>

@include('layouts.header')


    @php
        $pc = fn (string $section) => \App\Models\HomeContent::pageRows($contents, $section);
        $img = fn (?string $path, string $fallback = 'images/main.jpg') => asset($path && file_exists(public_path($path)) ? $path : $fallback);
        $href = fn (?string $url) => \Illuminate\Support\Str::startsWith($url ?? '', ['http', '#', 'mailto:', 'tel:']) ? $url : url($url ?: '/#contact');
        $lines = fn (?string $text) => array_pad(explode("\n", str_replace("\r", '', trim((string) $text)), 2), 2, '');
        $hero = $pc('experiences_page_hero')->first();
        $signatureHeading = $pc('experiences_page_signature_header')->first();
        $moreHeading = $pc('experiences_page_more_header')->first();
        $cta = $pc('experiences_page_cta')->first();
    @endphp

<main class="experience-page">

    {{-- Hero --}}
    <section class="experience-hero">
        <div class="hero-introduction">
            <p class="hero-label">{{ $hero?->subtitle }}</p>
            <h1>{!! nl2br(e($hero?->title)) !!}</h1>
            <p class="hero-description">{{ $hero?->description }}</p>
        </div>

        <div class="hero-image">
            <img src="{{ $img($hero?->image, 'images/exp1.jpg') }}" alt="{{ trim(strtok((string) $hero?->title, "\n")) }}">
        </div>
    </section>

    {{-- Signature experiences --}}
    <section class="experiences-area">
        <div class="section-heading">
            <span></span>
            <h2>{{ $signatureHeading?->title }}</h2>
            <span></span>
        </div>

        <div class="signature-grid">
            @foreach($pc('experiences_page_signature') as $card)
                <article class="signature-card">
                    <div class="signature-image">
                        <img src="{{ $img($card->image, 'images/exp2.jpg') }}" alt="{{ $card->title }}">
                    </div>
                    <div class="signature-title">{{ $card->title }}</div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- More experiences --}}
    <section class="experiences-area more-experiences">
        <div class="section-heading">
            <span></span>
            <h2>{{ $moreHeading?->title }}</h2>
            <span></span>
        </div>

        <div class="more-grid">
            @foreach($pc('experiences_page_more') as $card)
                <article class="more-card">
                    <div class="more-image">
                        <img src="{{ $img($card->image, 'images/exp3.jpg') }}" alt="{{ $card->title }}">
                    </div>
                    <div class="more-content">
                        <h3>{{ $card->title }}</h3>
                        <p>{{ $card->description }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Bottom concierge bar --}}
    <section class="concierge-bar">
        @foreach($pc('experiences_page_concierge') as $note)
            <div class="concierge-item">
                @if(filled($note->icon))
                    <div class="concierge-icon {{ str_contains((string) $note->icon, 'whatsapp') ? 'whatsapp' : '' }}">
                        <x-icon :name="$note->icon" :size="46" />
                    </div>
                @endif
                <div>
                    <h3>{{ $note->title }}</h3>
                    <p>{!! nl2br(e($note->description)) !!}</p>
                </div>
            </div>
        @endforeach

        @if($cta && filled($cta->button_text))
            <a href="{{ $href($cta->button_url) }}" class="plan-button">
                <span class="plan-logo"><img src="{{ asset(\App\Models\HomeContent::siteLogo()) }}" alt="" width="34" height="34"></span>
                {{ $cta->button_text }}
            </a>
        @endif
    </section>

</main>

    @include('layouts.footer')

</body>

</html>