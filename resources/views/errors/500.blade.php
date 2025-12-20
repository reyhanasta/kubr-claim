<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error | {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    @fluxAppearance
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-8">
    <div class="max-w-md w-full text-center">
        <!-- Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-rose-100 dark:bg-rose-900/20">
                <svg class="w-12 h-12 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Content -->
        <div class="space-y-4">
            <h1 class="text-6xl font-bold text-gray-900 dark:text-gray-50">500</h1>
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Server Error</h2>
            <p class="text-gray-600 dark:text-gray-400">
                Maaf, terjadi masalah pada server kami. .
            </p>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Kembali ke Dashboard
            </a>

        </div>

        @if(app()->environment('local'))
            <!-- Debug Info (only in local) -->
            <div class="mt-8 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg text-left text-xs">
                <details class="text-gray-700 dark:text-gray-300">
                    <summary class="cursor-pointer font-semibold mb-2">Debug Info</summary>
                    <div class="space-y-1 font-mono">
                        <p><strong>Error:</strong> {{ $exception->getMessage() ?? 'Unknown error' }}</p>
                        <p><strong>File:</strong> {{ $exception->getFile() ?? 'Unknown' }}</p>
                        <p><strong>Line:</strong> {{ $exception->getLine() ?? 'Unknown' }}</p>
                    </div>
                </details>
            </div>
        @endif

        <!-- Footer -->
        <div class="mt-12 text-sm text-gray-500 dark:text-gray-400">
            <p>Masalah berlanjut? <a href="mailto:{{ config('mail.from.address') }}"
                    class="text-emerald-600 dark:text-emerald-400 hover:underline">Hubungi Support</a></p>
        </div>
    </div>

    @fluxScripts
</body>

</html>