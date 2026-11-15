{{-- Tab title, search description, link-preview tags and statistics for a public page.
     Edited in the admin under Site-wide > Search and sharing (see App\Support\SiteSeo).
     Pass: seoPage (page name slug), seoTitle / seoDescription / seoImage (fallbacks), seoNoindex. --}}
@php
    $seo = \App\Support\SiteSeo::tags($seoPage ?? '', $seoTitle ?? '', $seoDescription ?? '', $seoImage ?? null);
@endphp
<title>{{ $seo['title'] }}</title>
@if ($seo['description'] !== '')
    <meta name="description" content="{{ $seo['description'] }}">
@endif
@if (! empty($seoNoindex))
    <meta name="robots" content="noindex">
@else
    <link rel="canonical" href="{{ $seo['canonical'] }}">
@endif
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $seo['site'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
@if ($seo['description'] !== '')
    <meta property="og:description" content="{{ $seo['description'] }}">
@endif
@if ($seo['image'])
    <meta property="og:image" content="{{ $seo['image'] }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">
@endif
<meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
@if ($seo['analytics'] !== '' && empty($seoNoindex))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seo['analytics'] }}"></script>
    <script>window.dataLayer = window.dataLayer || []; function gtag() { dataLayer.push(arguments); } gtag('js', new Date()); gtag('config', '{{ $seo['analytics'] }}');</script>
@endif
