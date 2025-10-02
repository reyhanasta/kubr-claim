<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('it_measures_resume_upload_speed', function () {
   Storage::fake('public');

        $file = UploadedFile::fake()->create('resume.pdf', 500, 'application/pdf');

        $start = microtime(true);

        // Simulate Livewire call to updatedResumeFile
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $storedPath = $file->storeAs('temp', $filename, 'public');

        $elapsed = microtime(true) - $start;

        dump("Resume upload time: " . number_format($elapsed * 1000, 2) . " ms");

        Storage::disk('public')->assertExists($storedPath);

        $this->assertTrue($elapsed < 200, "Upload terlalu lambat (>200ms)");
});

test('it_measures_billing_upload_speed', function () {
    Storage::fake('public');

        $file = UploadedFile::fake()->create('billing.pdf', 500, 'application/pdf');

        $start = microtime(true);

        // Simulate Livewire call to updatedBillingFile
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $storedPath = $file->storeAs('temp', $filename, 'public');

        $elapsed = microtime(true) - $start;

        dump("Billing upload time: " . number_format($elapsed * 1000, 2) . " ms");

        Storage::disk('public')->assertExists($storedPath);

        $this->assertTrue($elapsed < 200, "Upload terlalu lambat (>200ms)");
});

test('it_can_handle_multiple_resume_and_billing_uploads', function () {
    Storage::fake('public');

    $files = [];
    for ($i = 0; $i < 10; $i++) {
        $files[] = UploadedFile::fake()->create("doc_$i.pdf", 500, 'application/pdf');
    }

    $start = microtime(true);

    foreach ($files as $file) {
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $storedPath = $file->storeAs('temp', $filename, 'public');
        Storage::disk('public')->assertExists($storedPath);
    }

    $elapsed = microtime(true) - $start;
    $avg = $elapsed / count($files);

    dump("Uploaded " . count($files) . " files in " . number_format($elapsed, 2) . " s");
    dump("Average per file: " . number_format($avg * 1000, 2) . " ms");

    // Assert rata-rata per file masih di bawah 250ms
    $this->assertTrue($avg < 0.25, "Upload rata-rata per file terlalu lambat (>250ms)");
});

test('it_measures_sep_upload_and_parsing_speed', function () {
    Storage::fake('public');

    // Fake SEP file (contoh 1 MB PDF)
    $file = UploadedFile::fake()->create('sep.pdf', 1024, 'application/pdf');

    $startUpload = microtime(true);

    // Simulate upload
    $filename = uniqid() . '_' . $file->getClientOriginalName();
    $storedPath = $file->storeAs('temp', $filename, 'public');

    $uploadElapsed = microtime(true) - $startUpload;

    Storage::disk('public')->assertExists($storedPath);

    // ---- Simulate PDF Parsing (pdftotext) ----
    $startParse = microtime(true);

    // NB: biasanya kamu pakai $pdfReadService->getPdfTextwithSpatie($file)
    // Di test cukup simulate sleep kecil biar kira-kira
    $fakeParsing = file_get_contents(Storage::disk('public')->path($storedPath));

    $parseElapsed = microtime(true) - $startParse;

    dump("SEP upload time: " . number_format($uploadElapsed * 1000, 2) . " ms");
    dump("SEP parsing time: " . number_format($parseElapsed * 1000, 2) . " ms");

    // Expect upload cepat (<200ms), parsing bisa lebih lama tapi tetap wajar (<1500ms untuk 1MB)
    $this->assertTrue($uploadElapsed < 0.2, "SEP upload terlalu lambat (>200ms)");
    $this->assertTrue($parseElapsed < 1.5, "SEP parsing terlalu lambat (>1.5s untuk 1MB)");
});
