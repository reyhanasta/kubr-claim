<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{ asset('FastClaim_Icon.png') }}" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('FastClaim_Icon.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance