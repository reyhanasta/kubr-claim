<?php

use App\Livewire\BackupDashboard;
use App\Livewire\Dashboard\BpjsClaimDashboard;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Clinic;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', BpjsClaimDashboard::class)
        ->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // Admin Only Routes
    Route::middleware(['admin'])->group(function () {
        Route::get('backup', BackupDashboard::class)->name('backup.dashboard');
        Route::get('settings/clinic', Clinic::class)->name('settings.clinic');
        Route::get('settings/storage', Storage::class)->name('settings.storage');
    });
});

Route::get('bpjs-rajal-form', \App\Livewire\BpjsRawatJalanForm::class)->middleware(['auth', 'verified'])->name('bpjs-rajal-form');

require __DIR__.'/auth.php';
