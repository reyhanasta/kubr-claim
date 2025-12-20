<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Maintenance Mode | {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    @fluxAppearance
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <!-- Icon -->
        <div class="mb-8">
            <div
                class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-100 dark:bg-amber-900/20">
                <svg class="w-12 h-12 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>

        <!-- Content -->
        <div class="space-y-4">
            <h1 class="text-6xl font-bold text-gray-900 dark:text-gray-50">503</h1>
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Sedang Maintenance</h2>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $exception->getMessage() ?: 'Kami sedang melakukan pemeliharaan sistem. Mohon kembali lagi dalam beberapa saat.' }}
            </p>
        </div>

        <!-- Estimated Time (if provided) -->
        @if(isset($exception) && method_exists($exception, 'retryAfter') && $exception->retryAfter)
            <div class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/10 rounded-lg">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    <strong>Estimasi selesai:</strong> {{ $exception->retryAfter }} detik lagi
                </p>
            </div>
        @endif

        <!-- Actions -->
        <div class="mt-8">
            <button onclick="window.location.reload()"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Coba Lagi
            </button>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-sm text-gray-500 dark:text-gray-400">
            <p>Pertanyaan? <a href="mailto:{{ config('mail.from.address') }}"
                    class="text-emerald-600 dark:text-emerald-400 hover:underline">Hubungi Support</a></p>
        </div>
    </div>

    <!-- Auto-refresh -->
    <script>
        // Auto-reload every 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>

    @fluxScripts
</body>

</html>