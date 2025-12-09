@props(['count' => 1])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @for($i = 0; $i < $count; $i++)
        <div
            class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <!-- Icon/Avatar placeholder -->
                <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg flex-shrink-0"></div>

                <!-- Content -->
                <div class="flex-1 space-y-3">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>

                <!-- Action placeholder -->
                <div class="w-20 h-8 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
        </div>
    @endfor
</div>