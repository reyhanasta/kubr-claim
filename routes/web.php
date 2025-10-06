<?php

use App\Livewire\Dashboard\BpjsClaimDashboard;
use App\Livewire\BpjsLIP;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Appearance;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');



// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::middleware(['auth'])->group(function () {
   Route::get('/dashboard/bpjs-claims', BpjsClaimDashboard::class)
    ->name('dashboard.bpjs-claims');
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('bpjs-claim-form', \App\Livewire\BpjsClaimForm::class)->middleware(['auth', 'verified'])->name('bpjs-claim-form');
Route::get('bpjs-rawat-jalan', \App\Livewire\BpjsRawatJalan::class)->middleware(['auth', 'verified'])->name('bpjs-rawat-jalan');
Route::get('bpjs-rajal-form', \App\Livewire\BpjsRawatJalanForm::class)->middleware(['auth', 'verified'])->name('bpjs-rajal-form');
Route::get('bpjs-rajal-form-edit', \App\Livewire\BpjsRawatJalanForm::class)->middleware(['auth', 'verified'])->name('bpjs-rajal-form-edit');
Route::get('bpjs-rajal-lip', \App\Livewire\BpjsRawatJalanLip::class)->middleware(['auth', 'verified'])->name('bpjs-rajal-lip');

require __DIR__.'/auth.php';
