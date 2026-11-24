<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261017">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.seo', ['seoPage' => 'winterresidenz', 'seoTitle' => $winter['header']['title'], 'seoDescription' => $winter['header']['text']])
    @vite([
        'resources/css/header.css',
        'resources/css/pages.css',
        'resources/css/theme.css',
        'resources/css/footer.css','resources/css/rule.css', 'resources/css/responsive.css',
        'resources/css/winter.css',
        'resources/js/app.js'
    ])
</head>
<body data-no-photo-viewer>
    @include('layouts.header')

    <main class="cv-page wr-main">
        @include('winter.block', ['winter' => $winter])
    </main>

    @include('layouts.footer')
</body>
</html>
