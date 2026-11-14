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

    <title>
        Our Villas | Casa Verde Cliff Resort & Spa
    </title>

    @vite([
        'resources/js/villa-photos.js',
        'resources/css/header.css',
        'resources/css/villa.css',
        'resources/css/villa-gallery.css',
        'resources/css/theme.css',
        'resources/css/footer.css','resources/css/rule.css',
        'resources/js/app.js'
    ])

</head>

<body>

{{-- =========================================================
     NAVIGATION
========================================================= --}}

@include('layouts.header')

@php
    $vStyle = \App\Models\HomeContent::pageRow($contents, 'villas_page_style');
    $vBottom = \App\Models\HomeContent::pageRows($contents, 'villas_page_bottom');
    $vBook = \App\Models\HomeContent::pageRow($contents, 'villas_page_book');
    $featureIconRows = \App\Models\HomeContent::pageRows($contents, 'villas_feature_icons');
    // Wording of the pop-up's "check availability" button: the same admin-editable label the villa pages use.
    $vCheckLabel = \App\Models\HomeContent::pageLabels($contents, 'villas_detail_labels')[2] ?? 'Check availability';
    $vHref = fn (?string $url) => \Illuminate\Support\Str::startsWith($url ?? '', ['http', '#', 'mailto:', 'tel:']) ? $url : url($url ?: '/book');
@endphp


{{-- =========================================================
     VILLAS PAGE
========================================================= --}}

<main>

    <section class="villas-section">


        {{-- =================================================
             PAGE HEADER
        ================================================== --}}

        @if($villasHeader)

            <div class="villas-header">

                <p class="section-label">
                    {{ $villasHeader->subtitle }}
                </p>

                <h1>
                    {{ $villasHeader->title }}
                </h1>

                <p class="section-description">
                    {{ $villasHeader->description }}
                </p>

            </div>

        @else

            <div class="villas-header">

                <p class="section-label">
                    OUR VILLAS
                </p>

                <h1>
                    Choose your private villa
                </h1>

                <p class="section-description">
                    Two distinctive villa styles, each designed
                    for privacy, comfort and slow island living.
                </p>

            </div>

        @endif



        {{-- =================================================
             VILLA CARDS
        ================================================== --}}

        @php
            // If at least one villa shows quick facts, the others reserve the same row so prices and buttons line up.
            $anyFacts = $villas->contains(fn ($v) => !empty($villaDetails[\Illuminate\Support\Str::slug($v->title)]['capacity'] ?? []));
        @endphp

        <div class="villa-container" style="--villa-count: {{ max(1, $villas->count()) }}">

            @foreach($villas as $villa)

                @php
                    $cardSlug = \Illuminate\Support\Str::slug($villa->title);
                    $cardFacts = $villaDetails[$cardSlug]['capacity'] ?? [];
                    $cardDetailsUrl = filled($villa->button_url) && $villa->button_url !== '#'
                        ? $villa->button_url
                        : route('villas.show', $cardSlug);
                @endphp

                <article class="villa-card">


                    {{-- IMAGE --}}

                    @php
                        $photos = $villaPhotos[\Illuminate\Support\Str::slug($villa->title)] ?? [];
                    @endphp

                    @if(count($photos) > 1)

                        {{-- Clicking the photo opens a gallery with all of the villa's photos --}}
                        <button
                            type="button"
                            class="villa-image-wrapper villa-photo-open"
                            data-vm-open="vm-{{ $loop->index }}"
                            aria-haspopup="dialog"
                            aria-label="View all {{ count($photos) }} photos of {{ $villa->title }}"
                        >
                            <img
                                src="{{ asset($villa->image) }}"
                                alt="{{ $villa->title }}"
                                class="villa-image"
                            >

                            <span class="villa-photo-count" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
                                View {{ count($photos) }} photos
                            </span>
                        </button>

                        @php
                            $vmId = 'vm-' . $loop->index;
                            $vmSlug = \Illuminate\Support\Str::slug($villa->title);
                            $vmDetailsUrl = filled($villa->button_url) && $villa->button_url !== '#'
                                ? $villa->button_url
                                : route('villas.show', $vmSlug);
                        @endphp

                        <dialog class="vm" id="{{ $vmId }}" data-vm aria-label="{{ $villa->title }} photos">
                            <div class="vm-shell">

                                <header class="vm-top">
                                    <div class="vm-heading">
                                        <h2 class="vm-title">{{ $villa->title }}</h2>
                                        @if($villa->subtitle)
                                            <p class="vm-tagline">{{ $villa->subtitle }}</p>
                                        @endif
                                    </div>

                                    <div class="vm-top-actions">
                                        <span class="vm-count" aria-live="polite" data-vm-count>1 / {{ count($photos) }}</span>
                                        <button type="button" class="vm-close" data-vm-close aria-label="Close photos">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                                        </button>
                                    </div>
                                </header>

                                <div class="vm-stage" tabindex="0" role="group" aria-roledescription="carousel" aria-label="{{ $villa->title }} photos">
                                    <button type="button" class="vm-arrow vm-arrow--prev" data-vm-prev aria-label="Previous photo">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 5-7 7 7 7"/></svg>
                                    </button>

                                    <div class="vm-frame">
                                        @foreach($photos as $photo)
                                            <figure
                                                class="vm-slide{{ $loop->first ? ' is-active' : '' }}"
                                                role="group"
                                                aria-roledescription="slide"
                                                aria-label="{{ $loop->iteration }} of {{ count($photos) }}"
                                                aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                                                style="--vm-bg: url('{{ asset($photo) }}')"
                                            >
                                                <img
                                                    src="{{ asset($photo) }}"
                                                    alt="{{ $villa->title }}, photo {{ $loop->iteration }}"
                                                    loading="lazy"
                                                    draggable="false"
                                                >
                                            </figure>
                                        @endforeach
                                    </div>

                                    <button type="button" class="vm-arrow vm-arrow--next" data-vm-next aria-label="Next photo">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 5 7 7-7 7"/></svg>
                                    </button>
                                </div>

                                <div class="vm-thumbs" aria-label="Choose a photo">
                                    @foreach($photos as $photo)
                                        <button
                                            type="button"
                                            class="vm-thumb"
                                            data-vm-go="{{ $loop->index }}"
                                            aria-label="Show photo {{ $loop->iteration }}"
                                            @if($loop->first) aria-current="true" @endif
                                        >
                                            <img src="{{ asset($photo) }}" alt="" loading="lazy" draggable="false">
                                        </button>
                                    @endforeach
                                </div>

                                <footer class="vm-foot">
                                    @if($villa->price)
                                        <p class="vm-price">
                                            {{ $vStyle?->title }}
                                            <strong>{{ number_format($villa->price) }}</strong>
                                            <span>{{ $vStyle?->description }}</span>
                                        </p>
                                    @endif

                                    <div class="vm-cta">
                                        <a href="{{ $vmDetailsUrl }}" class="vm-btn vm-btn--ghost">
                                            {{ $villa->button_text ?: ($vStyle?->button_text ?: 'View details') }}
                                        </a>
                                        <a href="{{ route('booking.villa', $vmSlug) }}" class="vm-btn vm-btn--solid">
                                            {{ $vCheckLabel }}
                                        </a>
                                    </div>
                                </footer>

                            </div>
                        </dialog>

                    @else

                        <div class="villa-image-wrapper">

                            <img
                                src="{{ asset($villa->image) }}"
                                alt="{{ $villa->title }}"
                                class="villa-image"
                            >

                        </div>

                    @endif


                    {{-- CONTENT --}}

                    <div class="villa-content">

                        <h2>

                            <span class="villa-symbol"><x-glyph :value="$vStyle?->icon" :em="1.1" /></span>

                            {{ $villa->title }}

                        </h2>


                        @if($villa->subtitle)

                            <p class="villa-tagline">

                                {{ $villa->subtitle }}

                            </p>

                        @endif


                        {{-- QUICK FACTS: only the ones filled in on the villa in the admin --}}

                        @if(!empty($cardFacts))

                            <ul class="villa-facts" aria-label="{{ $villa->title }} at a glance">

                                @if(!empty($cardFacts['guests']))
                                    <li>
                                        <x-icon name="family" :size="18" />
                                        <span>Sleeps {{ $cardFacts['guests'] }}</span>
                                    </li>
                                @endif

                                @if(!empty($cardFacts['bedrooms']))
                                    <li>
                                        <x-icon name="bed" :size="18" />
                                        <span>{{ $cardFacts['bedrooms'] }} {{ \Illuminate\Support\Str::plural('Bedroom', $cardFacts['bedrooms']) }}</span>
                                    </li>
                                @endif

                                @if(!empty($cardFacts['bathrooms']))
                                    <li>
                                        <x-icon name="bath" :size="18" />
                                        <span>{{ $cardFacts['bathrooms'] }} {{ \Illuminate\Support\Str::plural('Bathroom', $cardFacts['bathrooms']) }}</span>
                                    </li>
                                @endif

                            </ul>

                        @elseif($anyFacts)

                            <ul class="villa-facts villa-facts--placeholder" aria-hidden="true">
                                <li><x-icon name="family" :size="18" /><span>&nbsp;</span></li>
                            </ul>

                        @endif


                        @if($villa->price)

                            <p class="villa-price">

                                {{ $vStyle?->title }}
                                <strong>
                                    {{ number_format($villa->price) }}
                                </strong>

                                <span>
                                    {{ $vStyle?->description }}
                                </span>

                            </p>

                        @endif


                        {{-- FEATURES --}}

                        @php
                            $villaFeatures = is_string($villa->features)
                                ? json_decode($villa->features, true)
                                : $villa->features;

                            if (empty($villaFeatures)) {
                                $villaFeatures = config(
                                    'villas.' . $cardSlug . '.card_features',
                                    []
                                );
                            }
                        @endphp

                        @if(!empty($villaFeatures))

                            <ul class="villa-features">

                                @foreach($villaFeatures as $featureIndex => $feature)
                                    @php
                                        $featureIconRow = $featureIconRows->first(fn ($row) => \Illuminate\Support\Str::slug($row->title ?? '') === $cardSlug && (int) $row->sort_order === $featureIndex + 1);
                                        $featureIcon = $featureIconRow?->icon;
                                    @endphp

                                    <li>

                                        <span class="feature-icon">@if(filled($featureIcon))<x-icon :name="$featureIcon" :size="18" />@elseif(filled($vStyle?->image))<x-icon :name="$vStyle->image" :size="18" />@else{{ $vStyle?->subtitle }}@endif</span>

                                        <span>
                                            {{ $feature }}
                                        </span>

                                    </li>

                                @endforeach

                            </ul>

                        @endif


                        {{-- BUTTONS: always at the bottom of the card, so cards of different heights still line up --}}

                        <div class="villa-actions">

                            <a href="{{ $cardDetailsUrl }}" class="villa-button">

                                <span><x-glyph :value="$vStyle?->icon" :em="1.1" /></span>
                                {{ $villa->button_text ?: $vStyle?->button_text }}

                            </a>

                            <a href="{{ route('booking.villa', $cardSlug) }}" class="villa-button villa-button--ghost">

                                {{ $vCheckLabel }}

                            </a>

                        </div>

                    </div>

                </article>

            @endforeach

        </div>

    </section>



    {{-- =====================================================
         BOTTOM INFORMATION / CTA
    ====================================================== --}}

    <section class="villa-bottom">
        <div class="villa-bottom-content">
            @if($intro = $vBottom->first())
                <div class="bottom-intro">
                    <span class="bottom-icon"><x-glyph :value="$intro->icon" :em="1.6" /></span>
                    <div>
                        <h3>{!! nl2br(e($intro->title)) !!}</h3>
                        <p>{!! nl2br(e($intro->description)) !!}</p>
                    </div>
                </div>
            @endif

            @foreach($vBottom->slice(1) as $point)
                <div class="bottom-divider"></div>
                <div class="bottom-feature">
                    <span class="bottom-icon"><x-glyph :value="$point->icon" :em="1.6" /></span>
                    <p>{!! nl2br(e($point->description)) !!}</p>
                </div>
            @endforeach

            @if($vBook && filled($vBook->button_text))
                <a href="{{ $vHref($vBook->button_url) }}" class="bottom-book-button">
                    <span><x-glyph :value="$vBook->icon" :em="1.1" /></span>
                    {{ $vBook->button_text }}
                </a>
            @endif
        </div>
    </section>

</main>

    @include('layouts.footer')

</body>
</html>