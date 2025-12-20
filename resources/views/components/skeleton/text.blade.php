@props(['rows' => 3])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-3']) }}>
    @for($i = 0; $i < $rows; $i++)
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
    @endfor
</div>