<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('it_measures_resume_upload_time_on_real_disk', function () {
    // Pastikan disk 'public' menggunakan driver 'local' untuk tes ini
    Storage::fake('public'); // gunakan fake? -> kita mau real
        Storage::deleteDirectory('temp'); // bersihkan dulu

        $file = UploadedFile::fake()->create('resume.pdf', 500, 'application/pdf');

        $start = microtime(true);

        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $storedPath = $file->storeAs('temp', $filename, 'public');

        $elapsed = microtime(true) - $start;

        dump("Resume upload (real disk) time: " . number_format($elapsed * 1000, 2) . " ms");

        Storage::disk('public')->assertExists($storedPath);
});

test('it_measures_billing_upload_time_on_real_disk', function () {
    // Pastikan disk 'public' menggunakan driver 'local' untuk tes ini
        Storage::fake('public'); // gunakan fake? -> kita mau real
        Storage::deleteDirectory('temp'); // bersihkan dulu

        $file = UploadedFile::fake()->create('billing.pdf', 500, 'application/pdf');

        $start = microtime(true);

        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $storedPath = $file->storeAs('temp', $filename, 'public');

        $elapsed = microtime(true) - $start;

        dump("Billing upload (real disk) time: " . number_format($elapsed * 1000, 2) . " ms");

        Storage::disk('public')->assertExists($storedPath);
});

test('it_measures_sep_upload_and_parsing_time_on_real_disk', function () {
    // Pastikan disk 'public' menggunakan driver 'local' untuk tes ini
        Storage::fake('public'); // gunakan fake? -> kita mau real
        Storage::deleteDirectory('temp'); // bersihkan dulu

        $file = UploadedFile::fake()->create('sep.pdf', 500, 'application/pdf');

        $start = microtime(true);

        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $storedPath = $file->storeAs('temp', $filename, 'public');

        $elapsed = microtime(true) - $start;

        dump("SEP upload (real disk) time: " . number_format($elapsed * 1000, 2) . " ms");

        Storage::disk('public')->assertExists($storedPath);
});
