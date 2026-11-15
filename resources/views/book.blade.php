<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261017">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book your stay | Casa Verde Cliff Resort &amp; Spa</title>
    <meta name="description" content="Choose your villa, pick your dates and send your booking request to Casa Verde Cliff Resort &amp; Spa.">

    @vite([
        'resources/css/header.css',
        'resources/css/book.css',
        'resources/css/theme.css',
        'resources/css/footer.css', 'resources/css/rule.css',
        'resources/js/app.js',
        'resources/js/book.js',
    ])

    <style>
        /* Booking form: refined styling. Layout, fields and text are unchanged.
           The terms are no longer a scrolling box: a tap on "Read the full terms" opens them in full.
           Inline so it shows without a rebuild; can be moved into resources/css/book.css. */
        .bk {
            --bk-forest: #0a2f26;
            --bk-gold: #a47a44;
            --bk-gold-bright: #cfa66d;
            --bk-line: #e6dcc9;
            --bk-field: #d9cfba;
            --bk-shadow: 0 22px 36px -28px rgba(6, 33, 27, .55);
        }

        /* ---------- Intro steps ---------- */
        .bk-steps li { padding: 8px 16px 8px 8px; border: 1px solid var(--bk-line); border-radius: 999px; background: rgba(255, 255, 255, .7); }
        .bk-steps b { background: var(--bk-forest); color: #e6c28f; }

        /* ---------- Reserve button ---------- */
        .bk-reserve { border-radius: 12px; font-size: .88rem; letter-spacing: .12em; padding: 17px 22px; }

        /* ---------- Form panel ---------- */
        .bk .bk-panel {
            border-top: 0; border-left: 0;
            background: linear-gradient(180deg, #f6f0e4, #f2ebdc);
            position: relative;
        }
        .bk .bk-panel::before { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 3px; background: linear-gradient(90deg, var(--bk-gold-bright), rgba(207, 166, 109, .15)); }
        .bk .bk-form { padding: clamp(20px, 3.6vw, 44px); }
        .bk .bk-cols { gap: clamp(22px, 3.4vw, 40px); }

        /* Fields card */
        .bk .bk-fields {
            gap: 0; padding: clamp(22px, 3vw, 36px);
            border: 1px solid var(--bk-line); border-radius: 22px;
            background: #fffdf9; box-shadow: var(--bk-shadow);
        }
        .bk .bk-group { gap: 16px; padding: 0 0 clamp(22px, 3vw, 32px); }
        .bk .bk-group + .bk-group { padding-top: clamp(22px, 3vw, 32px); border-top: 1px solid var(--bk-line); }
        .bk .bk-group:last-child { padding-bottom: 0; }
        .bk .bk-group legend { gap: 12px; margin-bottom: 18px; font-size: 1.6rem; font-weight: 600; letter-spacing: 0; }
        .bk .bk-group legend b { width: 30px; height: 30px; background: var(--bk-forest); color: #e6c28f; font-size: .85rem; font-weight: 500; box-shadow: 0 0 0 4px rgba(207, 166, 109, .22); }

        /* Labels and inputs */
        .bk .bk-field { gap: 8px; }
        .bk .bk-field > span { font-size: .8rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #3a4a44; }
        .bk .bk-field small { font-size: .72rem; font-weight: 400; letter-spacing: .02em; text-transform: none; color: #7a857f; }
        .bk .bk-form :is(input[type="text"], input[type="email"], input[type="tel"], input[type="date"], input[type="number"], textarea) {
            min-height: 54px; padding: 13px 16px;
            border: 1.5px solid var(--bk-field); border-radius: 12px;
            background: #fff; font-size: 1.02rem; color: var(--bk-ink);
            color-scheme: light;
        }
        .bk .bk-form textarea { min-height: 112px; line-height: 1.55; }
        .bk .bk-form :is(input[type="text"], input[type="email"], input[type="tel"], input[type="date"], textarea):hover { border-color: #c4b79c; }
        .bk .bk-form :is(input, textarea):focus { border-color: var(--bk-gold-bright); box-shadow: 0 0 0 4px rgba(207, 166, 109, .28); }
        .bk .bk-form input::placeholder, .bk .bk-form textarea::placeholder { color: #a2a8a3; }
        .bk .bk-form input[type="date"]::-webkit-calendar-picker-indicator { padding: 6px; border-radius: 8px; cursor: pointer; opacity: .7; }
        .bk .bk-form input[type="date"]::-webkit-calendar-picker-indicator:hover { background: #f3ead8; opacity: 1; }
        .bk .bk-hint { padding: 10px 14px; border-radius: 10px; background: #f6efe0; font-size: .9rem; color: #5d5a4c; }

        /* Guest stepper */
        .bk .bk-stepper { grid-template-columns: 54px 1fr 54px; border: 1.5px solid var(--bk-field); border-radius: 12px; background: #fff; transition: border-color .2s, box-shadow .2s; }
        .bk .bk-stepper:focus-within { border-color: var(--bk-gold-bright); box-shadow: 0 0 0 4px rgba(207, 166, 109, .28); }
        .bk .bk-stepper button { background: #f6efe0; font-size: 1.5rem; color: var(--bk-forest); }
        .bk .bk-stepper button:hover:not(:disabled) { background: var(--bk-forest); color: #e6c28f; }
        .bk .bk-stepper button:focus-visible { outline: 2px solid var(--bk-gold); outline-offset: -3px; }
        .bk .bk-form .bk-stepper input[type="number"] { min-height: 52px; font-size: 1.15rem; border: 0; }

        /* ---------- Side column ---------- */
        .bk .bk-side { gap: 18px; }
        .bk .bk-side h3 { font-size: 1.5rem; }

        .bk .bk-quote {
            position: relative; padding: 26px 24px 22px; overflow: hidden;
            border: 1px solid var(--bk-line); border-radius: 22px; background: #fff; box-shadow: var(--bk-shadow);
        }
        .bk .bk-quote::before { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 4px; background: linear-gradient(90deg, var(--bk-gold-bright), #e8c797); }
        .bk .bk-quote-empty { padding: 14px 16px; border-radius: 12px; background: #f9f4e8; font-size: .96rem; line-height: 1.55; color: #5d5a4c; }
        .bk .bk-quote dl { gap: 12px; }
        .bk .bk-quote dl > div { font-size: 1rem; }
        .bk .bk-quote .is-total { margin-top: 6px; padding-top: 16px; }
        .bk .bk-quote .is-total dd { font-size: 1.9rem; }
        .bk .bk-quote-foot { margin-top: 16px; padding-top: 14px; border-top: 1px dashed #d8cdb6; font-size: .86rem; }

        /* Terms: full text on demand, no scrolling box */
        .bk .bk-terms { padding: 22px 22px 20px; border: 1px solid #e6d3a6; border-radius: 22px; background: linear-gradient(180deg, #fdf7e8, #faf1da); }
        .bk .bk-terms h3 { margin-bottom: 14px; font-family: var(--cv-serif, serif); font-size: 1.5rem; color: var(--bk-forest); }
        .bk .bk-terms h3 svg { flex: none; box-sizing: content-box; width: 17px; height: 17px; padding: 8px; border-radius: 50%; background: var(--bk-forest); color: #e6c28f; }
        .bk .bk-terms-more { border: 1px solid #e3cf9d; border-radius: 14px; background: rgba(255, 255, 255, .6); }
        .bk .bk-terms-more summary {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            min-height: 48px; padding: 0 16px; list-style: none; cursor: pointer;
            font-size: .8rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--bk-forest);
            border-radius: 14px; transition: background-color .2s;
        }
        .bk .bk-terms-more summary::-webkit-details-marker { display: none; }
        .bk .bk-terms-more summary:hover { background: rgba(255, 255, 255, .85); }
        .bk .bk-terms-more summary:focus-visible { outline: 2px solid var(--bk-gold); outline-offset: 2px; }
        .bk .bk-terms-more summary::after { content: ""; flex: none; width: 9px; height: 9px; border-right: 2px solid var(--bk-gold); border-bottom: 2px solid var(--bk-gold); transform: translateY(-2px) rotate(45deg); transition: transform .25s; }
        .bk .bk-terms-more[open] summary::after { transform: translateY(2px) rotate(225deg); }
        .bk .bk-terms-more .t-close, .bk .bk-terms-more[open] .t-open { display: none; }
        .bk .bk-terms-more[open] .t-close { display: inline; }
        .bk .bk-terms-text { max-height: none; overflow: visible; padding: 4px 18px 18px; font-size: .92rem; line-height: 1.8; color: #50493c; border-top: 1px solid #ecdcb4; margin-top: 2px; padding-top: 16px; }

        .bk .bk-check { gap: 13px; margin-top: 18px; font-size: 1rem; line-height: 1.4; }
        .bk .bk-check input {
            -webkit-appearance: none; appearance: none;
            display: grid; place-content: center; flex: none;
            width: 26px; height: 26px; margin: 0;
            border: 1.5px solid var(--bk-gold); border-radius: 8px; background: #fff; cursor: pointer;
            transition: background-color .2s, border-color .2s, box-shadow .2s;
        }
        .bk .bk-check input::after {
            content: ""; width: 14px; height: 14px; transform: scale(0); transition: transform .15s ease;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23e6c28f' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7'/%3E%3C/svg%3E") center / contain no-repeat;
        }
        .bk .bk-check input:checked { border-color: var(--bk-forest); background: var(--bk-forest); }
        .bk .bk-check input:checked::after { transform: scale(1); }
        .bk .bk-check input:focus-visible { outline: 3px solid rgba(207, 166, 109, .6); outline-offset: 2px; }
        .bk .bk-check input.is-invalid { border-color: var(--bk-danger); box-shadow: 0 0 0 4px rgba(166, 61, 47, .15); outline: 0; }

        /* Submit */
        .bk .bk-submit {
            min-height: 60px; border-radius: 14px;
            background: linear-gradient(135deg, #e0b982, #c4955a); color: #1a1208;
            font-size: .95rem; font-weight: 700; letter-spacing: .14em;
            box-shadow: 0 18px 30px -16px rgba(122, 85, 40, .9);
        }
        .bk .bk-submit:hover:not(:disabled) { filter: none; background: linear-gradient(135deg, #e8c797, #cfa066); transform: translateY(-2px); box-shadow: 0 22px 32px -16px rgba(122, 85, 40, 1); }
        .bk .bk-submit:focus-visible { outline: 3px solid var(--bk-forest); outline-offset: 3px; }
        .bk .bk-submit.is-busy span::after { border-color: rgba(26, 18, 8, .3); border-top-color: #1a1208; }
        .bk .bk-secure { display: flex; align-items: center; justify-content: center; gap: 8px; margin: -4px 0 0; font-size: .84rem; }
        .bk .bk-secure::before {
            content: ""; width: 14px; height: 14px; flex: none;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a47a44' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='4' y='11' width='16' height='10' rx='2'/%3E%3Cpath d='M8 11V7a4 4 0 0 1 8 0v4'/%3E%3C/svg%3E") center / contain no-repeat;
        }

        .bk .bk-alert { border-radius: 14px; }

        /* Do not stick the side column while the terms are open (it would be taller than the screen) */
        .bk .bk-side:has(.bk-terms-more[open]) { position: static; }

        @media (max-width: 640px) {
            .bk .bk-fields { padding: 20px 16px; border-radius: 18px; }
            .bk .bk-group legend { font-size: 1.4rem; }
            .bk .bk-steps li { font-size: .86rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .bk .bk-check input, .bk .bk-check input::after, .bk .bk-terms-more summary::after { transition: none; }
        }
    </style>
</head>

<body>

@include('layouts.header')

@php
    $openSlug = $open ?: old('_villa');
    $peso = fn ($n) => '₱'.number_format((float) $n, 2);
    $dateLabel = fn ($d) => $d->format('M d');
@endphp

<main class="bk">

    <header class="bk-head">
        <span class="bk-eyebrow">{{ $page->subtitle ?? 'Book your stay' }}</span>
        <span class="bk-tick" aria-hidden="true"></span>
        <h1>{{ $page->title ?? 'Reserve your villa' }}</h1>
        <p>{{ $page->description ?? 'Choose your villa, pick your dates and send your request. Our team confirms it by email.' }}</p>

        <ol class="bk-steps" aria-label="How booking works">
            <li><b>1</b> {{ $L[1] }}</li>
            <li><b>2</b> {{ $L[2] }}</li>
            <li><b>3</b> {{ $L[3] }}</li>
        </ol>
    </header>

    @if (! $bookingOpen)
        <div class="bk-paused" role="status">
            <p>{!! nl2br(e($closedMessage)) !!}</p>
            <a href="{{ route('contact.index') }}" class="bk-btn-solid">Contact us</a>
        </div>
    @elseif ($villas === [])
        <p class="bk-empty">Online booking is not available yet. Please <a href="{{ route('contact.index') }}">contact us</a> to reserve.</p>
    @endif

    @foreach ($villas as $slug => $villa)
        @php
            $promoOn = \App\Support\BookingRates::promoVisible($villa);
            $isOpen = $openSlug === $slug;
            $mine = old('_villa') === $slug;          // this form was just submitted with errors
            $shownRate = $promoOn ? $villa['promo'] : $villa['regular'];
            $photos = $villa['photos'];
            $rates = [
                'regular' => $villa['regular'],
                'promo' => $promoOn ? $villa['promo'] : null,
                'from' => $promoOn ? $villa['from']->toDateString() : null,
                'to' => $promoOn ? $villa['to']->toDateString() : null,
                'maxNights' => $maxNights,
            ];
        @endphp

        <article class="bk-villa" id="{{ $slug }}" data-bk-villa data-open="{{ $isOpen ? 'true' : 'false' }}">

            @php($total = count($photos))
            <div class="bk-media" data-bk-gallery>
                @if ($total)
                    <div class="bk-mosaic bk-n{{ min($total, 5) }}">
                        @foreach (array_slice($photos, 0, 5) as $i => $photo)
                            <button type="button" class="bk-tile bk-t{{ $i + 1 }}" data-bk-open="{{ $i }}" aria-label="Open photo {{ $i + 1 }} of {{ $total }}">
                                <img src="{{ asset($photo) }}" alt="{{ $villa['title'] }} photo {{ $i + 1 }}" data-bk-img @if ($i > 0) loading="lazy" @endif>
                            </button>
                        @endforeach

                        {{-- Photos past the fifth only open in the viewer --}}
                        @foreach (array_slice($photos, 5) as $j => $photo)
                            <img src="{{ asset($photo) }}" alt="{{ $villa['title'] }} photo {{ $j + 6 }}" data-bk-img hidden>
                        @endforeach

                        <a href="{{ route('villas.index') }}" class="bk-chip bk-back">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            Go back
                        </a>

                        <button type="button" class="bk-chip bk-expand" data-bk-open="0" aria-label="Expand photos">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                            <span>Expand</span>
                        </button>

                        @if ($total > 1)
                            <button type="button" class="bk-chip bk-all" data-bk-open="0">Show all {{ $total }} photos</button>
                        @endif
                    </div>
                @else
                    <div class="bk-nophoto"><span>{{ $villa['title'] }}</span></div>
                @endif
            </div>

            <div class="bk-body">

                <div class="bk-summary">
                    <h2>{{ $villa['title'] }}</h2>

                    <p class="bk-price">
                        @if ($promoOn)
                            <s>{{ $peso($villa['regular']) }}</s>
                        @endif
                        <strong>{{ $peso($shownRate) }}</strong><span> {{ $L[6] }}</span>
                    </p>

                    @if ($promoOn)
                        <span class="bk-promo">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2c1 4-3 5-3 9a3 3 0 0 0 6 0c0-1-.4-1.8-1-2.5 2.5 1 5 3.6 5 7a7 7 0 0 1-14 0c0-6 7-8 7-13.5z"/></svg>
                            {{ $L[8] }} {{ strtoupper($dateLabel($villa['from'])) }} – {{ strtoupper($dateLabel($villa['to'])) }}, {{ $villa['to']->format('Y') }}
                        </span>
                    @endif

                    <p class="bk-note">{{ $L[7] }}</p>
                </div>

                <div class="bk-about">
                    <p>{{ $villa['description'] }}</p>

                    <button type="button" class="bk-reserve" data-bk-toggle aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="panel-{{ $slug }}" data-open-text="{{ $L[4] }}" data-close-text="{{ $L[5] }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16.5" rx="2"/><path d="M8 2.5v4M16 2.5v4M3 10h18"/></svg>
                        <span data-bk-toggle-text>{{ $isOpen ? $L[5] : $L[4] }}</span>
                    </button>
                </div>

            </div>

            {{-- ===================== Request form ===================== --}}
            <div class="bk-panel" id="panel-{{ $slug }}" @unless ($isOpen) hidden @endunless>
                <form method="POST" action="{{ route('booking.store', $slug) }}" class="bk-form" novalidate data-bk-form
                      data-rates='@json($rates)'>
                    @csrf
                    <input type="hidden" name="_villa" value="{{ $slug }}">
                    <div class="bk-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                    @if ($mine && $errors->any())
                        <div class="bk-alert" role="alert" tabindex="-1" data-bk-alert>
                            <strong>Please check the highlighted fields.</strong>
                            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="bk-cols">
                        <div class="bk-fields">

                            <fieldset class="bk-group">
                                <legend><b>1</b> {{ $L[9] }}</legend>
                                <div class="bk-row">
                                    <label class="bk-field">
                                        <span>Check-in</span>
                                        <input type="date" name="check_in" required min="{{ now()->toDateString() }}" value="{{ $mine ? old('check_in') : '' }}" data-bk-in
                                               @class(['is-invalid' => $mine && $errors->has('check_in')])>
                                    </label>
                                    <label class="bk-field">
                                        <span>Check-out</span>
                                        <input type="date" name="check_out" required min="{{ now()->addDay()->toDateString() }}" value="{{ $mine ? old('check_out') : '' }}" data-bk-out
                                               @class(['is-invalid' => $mine && $errors->has('check_out')])>
                                    </label>
                                </div>
                                <p class="bk-hint" data-bk-nights-hint>Select your dates to see the price.</p>
                            </fieldset>

                            <fieldset class="bk-group">
                                <legend><b>2</b> {{ $L[10] }}</legend>
                                <div class="bk-row">
                                    @foreach ([['adults', 'Adults', 'Age 13+', 1], ['children', 'Children', 'Age 0–12', 0]] as [$key, $label, $sub, $min])
                                        <div class="bk-field">
                                            <span>{{ $label }} <small>{{ $sub }}</small></span>
                                            <div class="bk-stepper" data-bk-stepper data-min="{{ $min }}" data-max="{{ $maxGuests }}">
                                                <button type="button" data-step="-1" aria-label="Fewer {{ strtolower($label) }}">−</button>
                                                <input type="number" name="{{ $key }}" min="{{ $min }}" max="{{ $maxGuests }}" inputmode="numeric" required
                                                       value="{{ $mine ? old($key, $key === 'adults' ? 2 : 0) : ($key === 'adults' ? 2 : 0) }}" aria-label="{{ $label }}">
                                                <button type="button" data-step="1" aria-label="More {{ strtolower($label) }}">+</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </fieldset>

                            <fieldset class="bk-group">
                                <legend><b>3</b> {{ $L[11] }}</legend>
                                <label class="bk-field">
                                    <span>Full name</span>
                                    <input type="text" name="name" required maxlength="120" autocomplete="name" placeholder="Your full name"
                                           value="{{ $mine ? old('name') : '' }}" @class(['is-invalid' => $mine && $errors->has('name')])>
                                </label>
                                <div class="bk-row">
                                    <label class="bk-field">
                                        <span>Email address</span>
                                        <input type="email" name="email" required maxlength="150" autocomplete="email" placeholder="you@example.com"
                                               value="{{ $mine ? old('email') : '' }}" @class(['is-invalid' => $mine && $errors->has('email')])>
                                    </label>
                                    <label class="bk-field">
                                        <span>Phone number</span>
                                        <input type="tel" name="phone" required maxlength="40" autocomplete="tel" placeholder="+63 900 000 0000"
                                               value="{{ $mine ? old('phone') : '' }}" @class(['is-invalid' => $mine && $errors->has('phone')])>
                                    </label>
                                </div>
                                <label class="bk-field">
                                    <span>Country of origin</span>
                                    <input type="text" name="country" required maxlength="80" autocomplete="country-name" placeholder="Country of origin"
                                           value="{{ $mine ? old('country') : '' }}" @class(['is-invalid' => $mine && $errors->has('country')])>
                                </label>
                                <label class="bk-field">
                                    <span>Special requests <small>Optional</small></span>
                                    <textarea name="special_requests" rows="3" maxlength="1000" placeholder="Arrival time, celebrations, dietary needs…">{{ $mine ? old('special_requests') : '' }}</textarea>
                                </label>
                            </fieldset>

                        </div>

                        <aside class="bk-side">
                            <div class="bk-quote" data-bk-quote aria-live="polite">
                                <h3>{{ $L[16] }}</h3>
                                <p class="bk-quote-empty" data-bk-empty>Choose your check-in and check-out dates to see your total.</p>
                                <dl data-bk-lines hidden>
                                    <div><dt data-bk-line-label></dt><dd data-bk-line-amount></dd></div>
                                    <div data-bk-save-row hidden class="is-save"><dt>Promo savings</dt><dd data-bk-save></dd></div>
                                    <div class="is-total"><dt>{{ $L[17] }}</dt><dd data-bk-total></dd></div>
                                </dl>
                                <p class="bk-quote-foot">{{ $L[13] }}</p>
                            </div>

                            <div class="bk-terms">
                                <h3>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                                    {{ $L[15] }}
                                </h3>
                                <details class="bk-terms-more">
                                    <summary><span class="t-open">Read the full terms</span><span class="t-close">Hide the terms</span></summary>
                                    <div class="bk-terms-text" role="region" aria-label="Terms and conditions for {{ $villa['title'] }}">{!! nl2br(e($villa['terms'])) !!}</div>
                                </details>
                                <label class="bk-check">
                                    <input type="checkbox" name="terms" value="1" required @checked($mine && old('terms')) @class(['is-invalid' => $mine && $errors->has('terms')])>
                                    <span>{{ $L[14] }}</span>
                                </label>
                            </div>

                            <button type="submit" class="bk-submit" data-bk-submit>
                                <span>{{ $L[12] }}</span>
                            </button>
                            <p class="bk-secure">Your details are only used to handle this booking.</p>
                        </aside>
                    </div>
                </form>
            </div>

        </article>
    @endforeach

</main>

{{-- Full-screen photo viewer shared by every villa --}}
<dialog class="bk-lightbox" data-bk-lightbox aria-label="Photo viewer">
    <button type="button" class="bk-lb-close" data-bk-lb-close aria-label="Close">×</button>
    <button type="button" class="bk-lb-nav bk-lb-prev" data-bk-lb-prev aria-label="Previous photo">‹</button>
    <img alt="" data-bk-lb-img>
    <button type="button" class="bk-lb-nav bk-lb-next" data-bk-lb-next aria-label="Next photo">›</button>
    <span class="bk-lb-count" data-bk-lb-count></span>
</dialog>

@include('layouts.footer')

</body>
</html>
