<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261017">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dining | Casa Verde Cliff Resort & Spa</title>

    @vite([
        'resources/css/header.css',
        'resources/css/dining.css',
        'resources/css/menubook.css',
        'resources/js/menubook.js',
        'resources/css/drinksmenu.css',
        'resources/js/drinksmenu.js',
        'resources/css/theme.css',
        'resources/css/footer.css','resources/css/rule.css',
        'resources/js/app.js'
    ])

    <style>
        /* Dining page: refined styling. Text, icons and layout are unchanged.
           Inline so it shows without a rebuild; can be moved into resources/css/dining.css. */
        .dining-page {
            --dn-forest: #06211b;
            --dn-gold: #c9a06a;
            --dn-gold-soft: #e3b078;
            --dn-sand: #f7f2ea;
            --dn-ink: #26342f;
            padding-top: 96px;
            overflow: visible;
            background:
                radial-gradient(ellipse 70% 40% at 50% 100%, rgba(201, 160, 106, .10), transparent 70%),
                var(--dn-sand);
        }

        /* ---------- Hero ---------- */
        .dining-page .dining-hero {
            position: relative;
            grid-template-columns: 39% 61%;
            min-height: 290px;
            border-bottom: 0;
            background: var(--dn-sand);
        }
        .dining-page .dining-hero::after {
            content: "";
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 160, 106, .7) 20%, rgba(201, 160, 106, .7) 80%, transparent);
        }
        .dining-page .dining-hero-content {
            position: relative;
            z-index: 2;
            padding: 28px 4% 28px 7%;
            background: var(--dn-sand);
        }
        .dining-page .dining-label {
            align-self: flex-start;
            margin: 0 0 10px;
            font-family: var(--cv-serif, serif);
            font-size: 1.1rem;
            font-weight: 500;
            letter-spacing: .24em;
            text-transform: uppercase;
            color: #b98b55;
        }
        .dining-page .dining-label::after { width: 54px; height: 1px; margin-top: 8px; background: var(--dn-gold); }
        .dining-page .dining-hero h1 {
            font-family: var(--cv-serif, serif);
            font-size: clamp(2.1rem, 3.3vw, 3.1rem);
            font-weight: 600;
            letter-spacing: .04em;
            line-height: 1.08;
            white-space: nowrap;
            color: var(--dn-forest);
        }
        .dining-page .dining-hero h2 {
            margin: 4px 0 18px;
            font-family: var(--cv-serif, serif);
            font-style: italic;
            font-weight: 400;
            font-size: clamp(1.6rem, 2.5vw, 2.3rem);
            letter-spacing: .04em;
            line-height: 1.15;
            color: #c98f4f;
        }
        .dining-page .dining-hero p {
            max-width: 400px;
            margin: 0;
            font-family: var(--cv-serif, serif);
            font-size: 1.12rem;
            line-height: 1.6;
            color: var(--dn-ink);
        }
        .dining-page .dining-hero-image { position: relative; min-height: 290px; overflow: hidden; }
        .dining-page .dining-hero-image img { transition: transform 1.6s cubic-bezier(.2, .6, .2, 1); }
        .dining-page .dining-hero:hover .dining-hero-image img { transform: scale(1.025); }
        .dining-page .dining-hero-image::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 22%;
            z-index: 1;
            background: linear-gradient(90deg, var(--dn-sand), rgba(247, 242, 234, 0));
            pointer-events: none;
        }
        .dining-page .dining-hero-image::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(6, 33, 27, 0) 55%, rgba(6, 33, 27, .22));
            pointer-events: none;
        }

        /* ---------- Highlights ---------- */
        .dining-page .dining-features { gap: 0; padding: 30px 3% 18px; }
        .dining-page .dining-features .dining-feature {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-width: 0;
            padding: 0 18px;
        }
        .dining-page .dining-features .dining-feature + .dining-feature { border-left: 1px solid rgba(201, 160, 106, .3); }

        .dining-page .dining-features .feature-icon {
            width: 84px; height: 84px; min-width: 84px; min-height: 84px;
            flex: 0 0 84px;
            box-sizing: border-box;
            display: grid; place-items: center;
            margin: 0 auto 16px;
            padding: 8px;
            overflow: hidden;
            line-height: 1;
            border-radius: 50%;
            border: 1px solid var(--dn-gold-soft);
            color: #d19a5b;
            background: radial-gradient(circle at 35% 30%, #fffdf8, #f6eddd 75%);
            box-shadow: 0 0 0 5px rgba(227, 176, 120, .12), 0 12px 24px -16px rgba(122, 83, 31, .55);
            transition: box-shadow .35s ease, transform .35s ease;
        }
        .dining-page .dining-features .dining-feature:hover .feature-icon {
            transform: translateY(-3px);
            box-shadow: 0 0 0 7px rgba(227, 176, 120, .2), 0 16px 26px -16px rgba(122, 83, 31, .6);
        }
        .dining-page .dining-features .feature-icon > svg,
        .dining-page .dining-features .feature-icon > img,
        .dining-page .dining-features .feature-icon > .cv-icon {
            display: block; position: static; margin: 0 auto; padding: 0;
            width: 62px !important; height: 62px !important;
            max-width: 100%; max-height: 100%; flex: none;
            object-fit: contain; transform: none !important; transform-origin: center;
            box-sizing: border-box;
            stroke-width: 1.25;
        }
        .dining-page .dining-features .dining-feature:nth-child(3) .feature-icon > svg,
        .dining-page .dining-features .dining-feature:nth-child(3) .feature-icon > img {
            width: 46px !important; height: 46px !important;
        }

        .dining-page .dining-feature h3 {
            margin: 0 0 8px;
            font-family: var(--cv-serif, serif);
            font-size: 1.08rem;
            font-weight: 700;
            letter-spacing: .06em;
            line-height: 1.25;
            color: var(--dn-forest);
        }
        .dining-page .feature-description {
            max-width: 240px;
            min-height: 50px;
            margin: 0 auto 16px;
            font-family: var(--cv-serif, serif);
            font-size: 1.08rem;
            line-height: 1.45;
            color: #46534e;
        }
        .dining-page .dining-feature img {
            width: 84%;
            max-width: 220px;
            height: auto;
            aspect-ratio: 188 / 158;
            margin: auto auto 0;
            object-fit: cover;
            border-radius: 22px;
            border: 3px solid #fffaf1;
            box-shadow: 0 18px 30px -18px rgba(6, 33, 27, .55), 0 0 0 1px rgba(201, 160, 106, .35);
            transition: transform .5s ease, box-shadow .5s ease;
        }
        .dining-page .dining-feature:hover > img {
            transform: translateY(-4px);
            box-shadow: 0 24px 34px -18px rgba(6, 33, 27, .6), 0 0 0 1px rgba(201, 160, 106, .6);
        }

        /* ---------- Bottom row ---------- */
        .dining-page .dining-bottom {
            gap: 24px;
            padding: 22px 4% 44px;
            grid-template-columns: 1fr 1.9fr 1fr;
        }
        .dining-page .bottom-info { gap: 16px; }
        .dining-page .bottom-icon { min-width: 56px; color: #c98f4f; opacity: .95; }
        .dining-page .bottom-icon svg { stroke-width: 1.1; }
        .dining-page .bottom-info h3 {
            margin: 0 0 4px;
            font-family: var(--cv-serif, serif);
            font-size: 1.08rem;
            font-weight: 700;
            letter-spacing: .06em;
            color: var(--dn-forest);
        }
        .dining-page .bottom-info p {
            margin: 0;
            font-family: var(--cv-serif, serif);
            font-size: 1.08rem;
            line-height: 1.4;
            color: #46534e;
        }
        .dining-page .dining-buttons { gap: 28px; }
        .dining-page .menu-button {
            min-width: 262px;
            min-height: 58px;
            padding: 0 30px;
            border-radius: 999px;
            font-family: var(--cv-serif, serif);
            font-size: 1.12rem;
            font-weight: 700;
            letter-spacing: .12em;
            background: var(--dn-forest);
            color: #e6c28f;
            border: 1.5px solid var(--dn-forest);
            box-shadow: 0 16px 26px -16px rgba(6, 33, 27, .85);
            transition: transform .25s ease, background-color .25s ease, color .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .dining-page .dining-buttons .menu-button:nth-child(even) {
            background: transparent;
            color: var(--dn-forest);
            border-color: var(--dn-gold);
            box-shadow: none;
        }
        .dining-page .menu-button:hover {
            transform: translateY(-2px);
            background: var(--dn-gold);
            border-color: var(--dn-gold);
            color: var(--dn-forest);
            box-shadow: 0 16px 26px -14px rgba(185, 139, 85, .9);
        }
        .dining-page .menu-button:focus-visible { outline: 2px solid var(--dn-gold); outline-offset: 4px; }

        @media (max-width: 1100px) {
            .dining-page .dining-hero { grid-template-columns: 1fr 1fr; }
            .dining-page .dining-hero h1,
            .dining-page .dining-hero h2 { white-space: normal; }
            .dining-page .dining-features { grid-template-columns: repeat(3, 1fr); gap: 34px 0; }
            .dining-page .dining-features .dining-feature + .dining-feature { border-left: 0; }
            .dining-page .dining-bottom { grid-template-columns: 1fr; }
        }
        @media (max-width: 700px) {
            .dining-page { padding-top: 76px; }
            .dining-page .dining-hero { grid-template-columns: 1fr; }
            .dining-page .dining-hero-content { padding: 32px 24px; }
            .dining-page .dining-hero-image::before { display: none; }
            .dining-page .dining-features { grid-template-columns: 1fr 1fr; gap: 30px 12px; }
            .dining-page .dining-features .dining-feature { padding: 0 6px; }
            .dining-page .dining-feature img { width: 90%; }
            .dining-page .dining-buttons { flex-direction: column; gap: 14px; }
            .dining-page .menu-button { min-width: 0; width: 100%; max-width: 320px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .dining-page *, .dining-page *::before, .dining-page *::after { transition: none !important; }
        }
    </style>

    <style>
        /* Food menu book: a dish added without a photo shows a neat plate placeholder instead of an empty circle */
        .mbp .mbp-photo--empty { display: grid; place-items: center; border: 1px solid rgba(201, 160, 106, .5); background: radial-gradient(circle at 35% 30%, #fbf6ec, #efe4d0 78%); color: #c9a06a; }
        .mbp .mbp-photo--empty svg { width: 46%; height: 46%; }
    </style>
</head>


<body>

    @include('layouts.header')


        @php
        $pc = fn (string $section) => \App\Models\HomeContent::pageRows($contents, $section);
        $img = fn (?string $path, string $fallback = 'images/main.jpg') => asset($path && file_exists(public_path($path)) ? $path : $fallback);
        $href = fn (?string $url) => \Illuminate\Support\Str::startsWith($url ?? '', ['http', '#', 'mailto:', 'tel:']) ? $url : url($url ?: '/#contact');
        $lines = fn (?string $text) => array_pad(explode("\n", str_replace("\r", '', trim((string) $text)), 2), 2, '');
        $hero = $pc('dining_page_hero')->first();
        [$heroTitle, $heroLine] = $lines($hero?->title);
        $info = $pc('dining_page_info');
        $menus = $pc('dining_page_menus');

        // Food menu book: opened by the EXPLORE FOOD MENU button (link #food-menu). Without a cover image it is off.
        $foodCover = $pc('dining_page_food_menu')->first();
        $foodBook = $foodCover && filled($foodCover->image) && file_exists(public_path($foodCover->image));
        $foodPages = $foodBook ? \App\Support\FoodMenu::pages($contents) : [];
        $opensFoodBook = fn ($menu): bool => $foodBook && (
            trim((string) $menu->button_url) === '#food-menu'
            || (in_array(trim((string) $menu->button_url), ['', '#'], true) && str_contains(strtolower((string) $menu->button_text), 'food'))
        );
        // Drinks menu: opened by the EXPLORE DRINKS MENU button. It is built from the admin (Dining > Drinks menu); with no visible drinks the button stays a normal link.
        $drinks = \App\Support\DrinksMenu::data($contents);
        $drinksMenu = $drinks['total'] > 0;
        $opensDrinksMenu = fn ($menu): bool => $drinksMenu && str_contains(strtolower((string) $menu->button_text), 'drink');
    @endphp

    <main class="dining-page">

        {{-- Hero --}}
        <section class="dining-hero">
            <div class="dining-hero-content">
                <span class="dining-label">{{ $hero?->subtitle }}</span>
                <h1>{{ $heroTitle }}</h1>
                @if($heroLine !== '')
                    <h2>{{ $heroLine }}</h2>
                @endif
                <p>{{ $hero?->description }}</p>
            </div>

            <div class="dining-hero-image">
                <img src="{{ $img($hero?->image, 'images/dining1.jpg') }}" alt="{{ trim($heroTitle) }}">
            </div>
        </section>

        {{-- Dining highlights --}}
        <section class="dining-features">
            @foreach($pc('dining_page_features') as $feature)
                <div class="dining-feature">
                    @if(filled($feature->icon))
                        <div class="feature-icon">
                            <x-icon :name="$feature->icon" :size="50" />
                        </div>
                    @endif

                    <h3>{{ $feature->title }}</h3>
                    <p class="feature-description">{{ $feature->description }}</p>

                    <img src="{{ $img($feature->image, 'images/dining2.jpg') }}" alt="{{ $feature->title }}">
                </div>
            @endforeach
        </section>

        {{-- Bottom information --}}
        <section class="dining-bottom">
            @if($left = $info->get(0))
                <div class="bottom-info">
                    @if(filled($left->icon))
                        <div class="bottom-icon"><x-icon :name="$left->icon" :size="46" /></div>
                    @endif
                    <div>
                        <h3>{{ $left->title }}</h3>
                        <p>{{ $left->description }}</p>
                    </div>
                </div>
            @endif

            <div class="dining-buttons">
                @foreach($menus as $menu)
                    @if(filled($menu->button_text))
                        @if($opensFoodBook($menu))
                            <a href="#food-menu" class="menu-button" role="button" data-menu-book="mb-food" aria-haspopup="dialog">{{ $menu->button_text }}</a>
                        @elseif($opensDrinksMenu($menu))
                            <a href="#drinks-menu" class="menu-button" role="button" data-drinks-menu="dm-drinks" aria-haspopup="dialog">{{ $menu->button_text }}</a>
                        @else
                            <a href="{{ $href($menu->button_url ?: '#') }}" class="menu-button">{{ $menu->button_text }}</a>
                        @endif
                    @endif
                @endforeach
            </div>

            @if($right = $info->get(1))
                <div class="bottom-info">
                    @if(filled($right->icon))
                        <div class="bottom-icon"><x-icon :name="$right->icon" :size="46" /></div>
                    @endif
                    <div>
                        <h3>{{ $right->title }}</h3>
                        <p>{{ $right->description }}</p>
                    </div>
                </div>
            @endif
        </section>

    </main>

    @if($foodBook)
        @include('partials.menu-book', [
            'id' => 'mb-food',
            'title' => $foodCover->title ?: 'Food menu',
            'cover' => $foodCover->image,
            'hint' => $foodCover->button_text ?: 'Click here',
            'help' => $foodCover->description,
            'pages' => $foodPages,
        ])
    @endif

    @if($drinksMenu)
        @include('partials.drinks-menu', ['id' => 'dm-drinks', 'menu' => $drinks])
    @endif

    @include('layouts.footer')

</body>
</html>