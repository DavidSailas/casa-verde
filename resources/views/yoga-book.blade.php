<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261017">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.seo', ['seoPage' => 'yoga-reset-book', 'seoTitle' => $copy['title'], 'seoDescription' => $copy['text']])

    @vite([
        'resources/css/header.css',
        'resources/css/book.css',
        'resources/css/theme.css',
        'resources/css/footer.css', 'resources/css/rule.css', 'resources/css/responsive.css',
        'resources/js/app.js',
        'resources/css/yoga-book.css',
        'resources/js/yoga-book.js',
    ])
</head>

<body>

@include('layouts.header')

@php
    $img = fn (?string $path) => $path && file_exists(public_path($path)) ? asset($path) : null;
    $photos = collect([$page['hero']['image'] ?? null])
        ->merge(collect($page['gallery']['items'] ?? [])->pluck('image'))
        ->map($img)->filter()->unique()->take(5)->values()->all();
    $total = count($photos);
    $icons = [
        'retreat' => '<path d="M12 3c1 4-3 5-3 9a3 3 0 0 0 6 0c0-1-.4-1.8-1-2.5 2.5 1 5 3.6 5 7a7 7 0 0 1-14 0c0-6 7-8 7-13.5z"/>',
        'class' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'private' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 21c0-4 3-6.5 7-6.5s7 2.5 7 6.5"/>',
    ];
    $selected = $selectedType;
@endphp

<main class="bk ybk">

    <header class="bk-head">
        <span class="bk-eyebrow">{{ $copy['eyebrow'] }}</span>
        <span class="bk-tick" aria-hidden="true"></span>
        <h1>{{ $copy['title'] }}</h1>
        <p>{{ $copy['text'] }}</p>

        <ol class="bk-steps ybk-progress" aria-label="How booking works">
            @foreach ($copy['steps'] as $step)
                <li data-ybk-step><a href="#ybk-s{{ $loop->iteration }}"><b>{{ $loop->iteration }}</b><span>{{ $step }}</span></a></li>
            @endforeach
        </ol>
    </header>

    <article class="bk-villa" id="yoga-reset">

        <div class="bk-media">
            @if ($total)
                <div class="bk-mosaic bk-n{{ min($total, 5) }}">
                    @foreach ($photos as $i => $photo)
                        <div class="bk-tile bk-t{{ $i + 1 }}">
                            <img src="{{ $photo }}" alt="Yoga Reset at Casa Verde, photo {{ $i + 1 }}" @if ($i > 0) loading="lazy" @endif>
                        </div>
                    @endforeach

                    <a href="{{ route('yoga.index') }}" class="bk-chip bk-back">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Go back
                    </a>
                </div>
            @else
                <div class="bk-nophoto"><span>Yoga Reset</span></div>
            @endif
        </div>

        <div class="bk-body">
            <div class="bk-summary">
                <h2>{{ $page['hero']['title'] }}</h2>
                @if (! empty($options['retreat']['price']))
                    <p class="bk-price"><span>Prices </span><strong>{{ $options['retreat']['price'] }}</strong></p>
                @endif
                @if ($priceNote)
                    <p class="bk-note">{{ $priceNote }}</p>
                @endif
            </div>
            <div class="bk-about">
                <p>{{ $page['hero']['text'] }}</p>
                @if (count($page['facts']))
                    <ul class="ybk-facts">
                        @foreach (array_slice($page['facts'], 0, 4) as $fact)<li>{{ $fact }}</li>@endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="bk-panel">
            <form method="POST" action="{{ route('yoga.book.store') }}" class="bk-form" novalidate data-ybk-form>
                @csrf
                <div class="bk-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                @if ($errors->any())
                    <div class="bk-alert" role="alert" tabindex="-1" data-bk-alert>
                        <strong>Please check the highlighted fields.</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="bk-cols">
                    <div class="bk-fields">

                        {{-- 1. Experience --}}
                        <fieldset class="bk-group" id="ybk-s1" data-ybk-section>
                            <legend><b>1</b> What would you like to book?</legend>
                            <p class="ybk-sub">Pick the experience that suits you best.</p>
                            <div class="ybk-options" role="radiogroup">
                                @foreach ($options as $key => $option)
                                    <label class="ybk-opt">
                                        <input type="radio" name="type" value="{{ $key }}" data-title="{{ $option['title'] }}" @checked($selected === $key) required>
                                        <span class="ybk-opt__card">
                                            <span class="ybk-opt__check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7"/></svg></span>
                                            <span class="ybk-opt__icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$key] !!}</svg></span>
                                            <strong>{{ $option['title'] }}</strong>
                                            <span class="ybk-opt__text">{{ $option['text'] }}</span>
                                            @if (! empty($option['price']))<span class="ybk-opt__tag">{{ $option['price'] }}</span>@endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            @if ($sessions)
                                <label class="bk-field {{ $selected === 'class' ? 'is-on' : '' }}" data-for="class">
                                    <span>Choose your class</span>
                                    <select name="session" data-ybk-session @class(['is-invalid' => $errors->has('session')])>
                                        <option value="">Select a class…</option>
                                        @foreach ($sessions as $name => $label)
                                            <option value="{{ $name }}" @selected($selectedSession === $name)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('session')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                                </label>
                            @endif
                        </fieldset>

                        {{-- 2. Date and guests --}}
                        <fieldset class="bk-group" id="ybk-s2" data-ybk-section>
                            <legend><b>2</b> Date &amp; guests</legend>
                            <p class="ybk-sub">Tell us when you would like to come and who is joining.</p>
                            <div class="bk-row">
                                <label class="bk-field">
                                    <span data-ybk-start-label>Arrival</span>
                                    <input type="date" name="start_date" required min="{{ now()->toDateString() }}" value="{{ old('start_date') }}" data-ybk-start @class(['is-invalid' => $errors->has('start_date')])>
                                    @error('start_date')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                                </label>
                                <label class="bk-field {{ $selected === 'retreat' ? 'is-on' : '' }}" data-for="retreat">
                                    <span>Departure</span>
                                    <input type="date" name="end_date" min="{{ now()->addDay()->toDateString() }}" value="{{ old('end_date') }}" data-ybk-end @class(['is-invalid' => $errors->has('end_date')])>
                                    @error('end_date')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                                </label>
                                <label class="bk-field {{ $selected === 'private' ? 'is-on' : '' }}" data-for="private">
                                    <span>Preferred time</span>
                                    <select name="time_pref" @class(['is-invalid' => $errors->has('time_pref')])>
                                        @foreach ($times as $time)
                                            <option value="{{ $time }}" @selected(old('time_pref', 'Flexible') === $time)>{{ $time }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <p class="bk-hint {{ $selected === 'retreat' ? 'is-on' : '' }}" data-ybk-hint data-for="retreat">Select your dates to see how many nights.</p>
                            <div class="ybk-nights {{ $selected === 'retreat' ? 'is-on' : '' }}" data-for="retreat" role="group" aria-label="Quick pick: length of stay">
                                <span>Quick pick</span>
                                @foreach ([3, 5, 7, 10] as $n)
                                    <button type="button" data-nights="{{ $n }}">{{ $n }} nights</button>
                                @endforeach
                            </div>

                            <div class="bk-row">
                                <div class="bk-field">
                                    <span>Participants</span>
                                    <div class="bk-stepper" data-ybk-stepper data-min="1" data-max="{{ $maxParticipants }}">
                                        <button type="button" data-step="-1" aria-label="Fewer participants">−</button>
                                        <input type="number" name="participants" min="1" max="{{ $maxParticipants }}" inputmode="numeric" required value="{{ old('participants', 1) }}" aria-label="Participants">
                                        <button type="button" data-step="1" aria-label="More participants">+</button>
                                    </div>
                                    @error('participants')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                                </div>
                                <label class="bk-field">
                                    <span>Yoga experience <small>Optional</small></span>
                                    <select name="level">
                                        <option value="">Not specified</option>
                                        @foreach ($levels as $level)
                                            <option value="{{ $level }}" @selected(old('level') === $level)>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </fieldset>

                        {{-- 3. Details --}}
                        <fieldset class="bk-group" id="ybk-s3" data-ybk-section>
                            <legend><b>3</b> Your details</legend>
                            <p class="ybk-sub">So our team knows where to send the reply.</p>
                            <label class="bk-field">
                                <span>Full name</span>
                                <input type="text" name="name" required maxlength="120" autocomplete="name" placeholder="Your full name" value="{{ old('name') }}" @class(['is-invalid' => $errors->has('name')])>
                                @error('name')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                            </label>
                            <div class="bk-row">
                                <label class="bk-field">
                                    <span>Email address</span>
                                    <input type="email" name="email" required maxlength="150" autocomplete="email" placeholder="you@example.com" value="{{ old('email') }}" @class(['is-invalid' => $errors->has('email')])>
                                    @error('email')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                                </label>
                                <label class="bk-field">
                                    <span>Phone number</span>
                                    <input type="tel" name="phone" required maxlength="40" autocomplete="tel" placeholder="+63 900 000 0000" value="{{ old('phone') }}" @class(['is-invalid' => $errors->has('phone')])>
                                    @error('phone')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                                </label>
                            </div>
                            <label class="bk-field">
                                <span>Country of origin</span>
                                <input type="text" name="country" required maxlength="80" autocomplete="country-name" placeholder="Country of origin" value="{{ old('country') }}" @class(['is-invalid' => $errors->has('country')])>
                                @error('country')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                            </label>
                            <label class="bk-field">
                                <span>Notes <small>Optional</small></span>
                                <textarea name="notes" rows="3" maxlength="1000" placeholder="Injuries, preferred teacher, celebrations, questions…">{{ old('notes') }}</textarea>
                            </label>
                        </fieldset>

                    </div>

                    <aside class="bk-side">
                        <div class="bk-quote" aria-live="polite">
                            <h3>Your request</h3>
                            <dl data-ybk-summary>
                                <div><dt>Experience</dt><dd data-sum="title">—</dd></div>
                                <div data-sum-row="session" hidden><dt>Class</dt><dd data-sum="session">—</dd></div>
                                <div><dt data-sum="start-label">Arrival</dt><dd data-sum="start">Choose a date</dd></div>
                                <div data-sum-row="end" hidden><dt>Departure</dt><dd data-sum="end">—</dd></div>
                                <div data-sum-row="nights" hidden><dt>Stay</dt><dd data-sum="nights">—</dd></div>
                                <div><dt>Participants</dt><dd data-sum="people">1</dd></div>
                                <div class="is-total"><dt>Price</dt><dd data-sum="price">Confirmed by our team</dd></div>
                            </dl>
                            <p class="bk-quote-foot">No payment is taken now. We reply by email to confirm.</p>
                        </div>

                        <div class="bk-terms">
                            <h3>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                                Good to know
                            </h3>
                            <ul class="ybk-terms">
                                @foreach (preg_split('/\R+/', trim($copy['terms'])) as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                            <label class="bk-check">
                                <input type="checkbox" name="terms" value="1" required @checked(old('terms')) @class(['is-invalid' => $errors->has('terms')])>
                                <span>{{ $copy['agree'] }}</span>
                            </label>
                            @error('terms')<em class="bk-err" role="alert">{{ $message }}</em>@enderror
                        </div>

                        <button type="submit" class="bk-submit" data-ybk-submit>
                            <span>{{ $copy['submit'] }}</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                        <p class="bk-secure">Your details are only used to handle this request.</p>
                    </aside>
                </div>
            </form>
        </div>
    </article>
</main>

{{-- Phones and tablets: a bar with the request summary and a jump to the send button --}}
<div class="ybk-bar" data-ybk-bar aria-hidden="true">
    <div class="ybk-bar__text">
        <small>Your request</small>
        <strong data-sum-bar>—</strong>
    </div>
    <button type="button" data-ybk-jump tabindex="-1">Review &amp; send</button>
</div>

@include('layouts.footer')

</body>
</html>
