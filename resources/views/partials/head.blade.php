<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
@if (request()->isSecure())
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
@endif
<title>{{ $title ?? config('app.name') }}</title>

{{-- Favicon dengan dark mode support --}}
<link rel="icon" href="{{ asset('FastClaim_Icon.png') }}" type="image/png" media="(prefers-color-scheme: light)">
<link rel="icon" href="{{ asset('fast-claim-web-bar.png') }}" type="image/png" media="(prefers-color-scheme: dark)">
<link rel="apple-touch-icon" href="{{ asset('FastClaim_Icon.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
@fluxAppearance