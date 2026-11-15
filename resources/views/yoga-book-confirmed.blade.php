<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    @include('layouts.seo', ['seoPage' => '', 'seoTitle' => 'Request received', 'seoNoindex' => true])
    @vite([
        'resources/css/header.css',
        'resources/css/book.css',
        'resources/css/theme.css',
        'resources/css/footer.css', 'resources/css/rule.css', 'resources/css/responsive.css',
        'resources/js/app.js',
        'resources/css/yoga-book.css',
    ])
</head>

<body>

@include('layouts.header')

<main class="bk">
    <section class="bk-done">
        <div class="bk-done-mark" aria-hidden="true">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <span class="bk-eyebrow">Request received</span>
        <h1>Thank you, {{ \Illuminate\Support\Str::before($booking->name, ' ') }}.</h1>
        <p class="bk-done-lead">We sent a confirmation of your request to {{ $booking->email }}. Our team will reply personally to confirm availability and details. Your place is not confirmed until you hear from us.</p>

        <dl class="bk-done-card">
            <div><dt>Reference</dt><dd class="bk-ref">{{ $booking->reference }}</dd></div>
            <div><dt>Experience</dt><dd>{{ $booking->option_title }}</dd></div>
            @if ($booking->session)<div><dt>Class</dt><dd>{{ $booking->session }}</dd></div>@endif
            <div><dt>{{ $booking->type === 'retreat' ? 'Arrival' : 'Preferred date' }}</dt><dd>{{ $booking->start_date->format('D, M j, Y') }}</dd></div>
            @if ($booking->end_date)<div><dt>Departure</dt><dd>{{ $booking->end_date->format('D, M j, Y') }} ({{ $booking->nights }} {{ \Illuminate\Support\Str::plural('night', $booking->nights) }})</dd></div>@endif
            @if ($booking->time_pref)<div><dt>Preferred time</dt><dd>{{ $booking->time_pref }}</dd></div>@endif
            <div><dt>Participants</dt><dd>{{ $booking->participants }}</dd></div>
        </dl>

        <ol class="ybk-next" aria-label="What happens next">
            <li><b>1</b><span>We review your request and check availability.</span></li>
            <li><b>2</b><span>Our team replies to you by email to confirm the details.</span></li>
            <li><b>3</b><span>Your place is reserved once you hear from us.</span></li>
        </ol>

        <div class="bk-done-actions">
            <a href="{{ url('/') }}" class="bk-btn-ghost">Back to home</a>
            <a href="{{ route('yoga.index') }}" class="bk-btn-solid">Back to Yoga Reset</a>
        </div>
    </section>
</main>

@include('layouts.footer')

</body>
</html>
