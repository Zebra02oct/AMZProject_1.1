<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? 'Presensi Siswa' }}</title>

@if (file_exists(public_path('images/logo.png')))
    @php
        $logoPath = public_path('images/logo.png');
        $logoVersion = filemtime($logoPath);
        $logoUrl = asset('images/logo.png') . '?v=' . $logoVersion;
    @endphp
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $logoUrl }}" />
    <link rel="icon" type="image/png" sizes="192x192" href="{{ $logoUrl }}" />
    <link rel="shortcut icon" href="{{ $logoUrl }}" />
    <link rel="apple-touch-icon" href="{{ $logoUrl }}" />
@endif

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
