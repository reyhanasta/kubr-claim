@props(['count' => 4])

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6']) }}>
    @for($i = 0; $i < $count; $i++)
        <div
            class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <!-- Icon -->
            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg mb-4"></div>

            <!-- Title -->
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-3"></div>

            <!-- Value -->
            <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>

            <!-- Subtitle -->
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
        </div>
    @endfor
</div>