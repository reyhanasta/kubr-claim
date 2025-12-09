@props(['columns' => 4, 'rows' => 5])

<div {{ $attributes->merge(['class' => 'animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden']) }}>
    <!-- Table Header -->
    <div class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 p-4">
        <div class="grid gap-4" style="grid-template-columns: repeat({{ $columns }}, 1fr);">
            @for($i = 0; $i < $columns; $i++)
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
            @endfor
        </div>
    </div>

    <!-- Table Rows -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @for($r = 0; $r < $rows; $r++)
            <div class="p-4">
                <div class="grid gap-4" style="grid-template-columns: repeat({{ $columns }}, 1fr);">
                    @for($c = 0; $c < $columns; $c++)
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    @endfor
                </div>
            </div>
        @endfor
    </div>
</div>