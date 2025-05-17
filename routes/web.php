<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('bpjs-claim-form', \App\Livewire\BpjsClaimForm::class)->middleware(['auth', 'verified'])->name('bpjs-claim-form');

Route::get('/preview-temp-file/{filename}', function ($filename) {
    $path = storage_path("app/livewire-tmp/{$filename}");

    // Make sure the file exists before attempting to serve it
    if (file_exists($path)) {
        $mimeType = mime_content_type($path);
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
        ]);
    }

    abort(404);
})->name('preview-temp-file');
require __DIR__.'/auth.php';
