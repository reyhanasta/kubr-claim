<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadPerformanceTest extends TestCase
{
    /** @test */
    public function it_measures_resume_upload_speed()
    {
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
    }

    /** @test */
    public function it_measures_billing_upload_speed()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('billing.pdf', 500, 'application/pdf');

        $start = microtime(true);

        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $storedPath = $file->storeAs('temp', $filename, 'public');

        $elapsed = microtime(true) - $start;

        dump("Billing upload time: " . number_format($elapsed * 1000, 2) . " ms");

        Storage::disk('public')->assertExists($storedPath);

        $this->assertTrue($elapsed < 200, "Upload terlalu lambat (>200ms)");
    }
}
