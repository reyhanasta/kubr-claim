<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Livewire Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Livewire-specific performance optimizations
    |
    */

    'lazy_load' => env('LIVEWIRE_LAZY_LOAD', true),

    'debounce_default' => env('LIVEWIRE_DEBOUNCE', 300), // milliseconds

    'defer_loading' => env('LIVEWIRE_DEFER_LOADING', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for dashboard and statistics
    |
    */

    'cache' => [
        'dashboard_ttl' => env('DASHBOARD_CACHE_TTL', 300), // 5 minutes
        'statistics_ttl' => env('STATISTICS_CACHE_TTL', 600), // 10 minutes
        'enabled' => env('CACHE_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure file upload optimization
    |
    */

    'uploads' => [
        'max_size' => env('MAX_UPLOAD_SIZE', 2048), // KB
        'allowed_types' => ['pdf', 'jpg', 'png', 'jpeg'],
        'chunk_size' => env('UPLOAD_CHUNK_SIZE', 1024), // KB
        'temp_cleanup_hours' => env('TEMP_CLEANUP_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure PDF processing optimization
    |
    */

    'pdf' => [
        'max_pages' => env('PDF_MAX_PAGES', 100),
        'compression_quality' => env('PDF_COMPRESSION_QUALITY', 75),
        'memory_limit' => env('PDF_MEMORY_LIMIT', '256M'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure queue-based processing
    |
    */

    'queue' => [
        'backup_enabled' => env('QUEUE_BACKUP_ENABLED', true),
        'backup_connection' => env('QUEUE_BACKUP_CONNECTION', 'database'),
        'backup_queue' => env('QUEUE_BACKUP_QUEUE', 'backups'),
    ],

];
