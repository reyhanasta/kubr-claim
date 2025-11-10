<?php

declare(strict_types=1);

use App\Livewire\BpjsRawatJalanForm;
use App\Models\User;
use App\Services\GenerateFolderService;
use App\Services\PdfMergerService;
use App\Services\PdfReadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('shared');
});

it('rejects non-pdf for lab result file', function () {
    $user = User::factory()->create();

    // Minimal mocks for SEP processing so component doesn't fail earlier
    $this->mock(PdfReadService::class, function ($mock) {
        $mock->shouldReceive('getPdfTextwithSpatie')->andReturn('X');
        $mock->shouldReceive('extractPdf')->andReturn([
            'patient_class' => '1',
            'medical_record_number' => 'RM001',
            'patient_name' => 'John Doe',
            'sep_number' => 'SEP123',
            'bpjs_serial_number' => '1234567890',
            'jenis_rawatan' => 'RJ',
            'sep_date' => '2025-11-10',
        ]);
    });

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('sep_number', 'SEP123')
        ->set('sep_date', '2025-11-10')
        ->set('medical_record_number', 'RM001')
        ->set('patient_name', 'John Doe')
        ->set('bpjs_serial_number', '1234567890')
        ->set('patient_class', '1')
        ->set('sepFile', UploadedFile::fake()->create('sep.pdf', 100))
        ->set('resumeFile', UploadedFile::fake()->create('resume.pdf', 100))
        ->set('billingFile', UploadedFile::fake()->create('billing.pdf', 100))
        ->set('labResultFile', UploadedFile::fake()->create('lab.jpg', 100))
        ->call('submit')
        ->assertHasErrors(['labResultFile']);
});

it('includes lab result in merge order after billing when provided', function () {
    $user = User::factory()->create();

    // Mock services
    $outputDir = 'claims/2025-11-10/SEP123_RJ/';

    $mergedOutput = Storage::disk('shared')->path($outputDir.'MERGED.pdf');

    $calledWithFiles = null;

    $this->mock(GenerateFolderService::class, function ($mock) use ($outputDir) {
        $mock->shouldReceive('generateOutputPath')
            ->andReturn($outputDir);
    });

    $this->mock(PdfMergerService::class, function ($mock) use (&$calledWithFiles, $mergedOutput) {
        $mock->shouldReceive('mergePdfs')
            ->once()
            ->andReturnUsing(function (array $files) use (&$calledWithFiles, $mergedOutput) {
                $calledWithFiles = $files;
                return $mergedOutput;
            });
        // Cleanup call can be ignored in this test
        $mock->shouldReceive('cleanupTempFiles')->andReturnTrue();
    });

    // Mock PDF read service so updatedSepFile succeeds
    $this->mock(PdfReadService::class, function ($mock) {
        $mock->shouldReceive('getPdfTextwithSpatie')
            ->andReturn('FAKE_PDF_TEXT');
        $mock->shouldReceive('extractPdf')
            ->andReturn([
                'patient_class' => 'Kelas 1',
                'medical_record_number' => 'RM001',
                'patient_name' => 'John Doe',
                'sep_number' => 'SEP123',
                'bpjs_serial_number' => '1234567890',
                'jenis_rawatan' => 'RJ',
                'sep_date' => '2025-11-10',
            ]);
    });

    $component = Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        // set minimal required fields
        ->set('sep_number', 'SEP123')
        ->set('sep_date', '2025-11-10')
        ->set('medical_record_number', 'RM001')
        ->set('patient_name', 'John Doe')
        ->set('bpjs_serial_number', '1234567890')
        ->set('patient_class', '1')
        // upload required files
        ->set('sepFile', UploadedFile::fake()->create('sep.pdf', 100))
        ->set('resumeFile', UploadedFile::fake()->create('resume.pdf', 100))
        ->set('billingFile', UploadedFile::fake()->create('billing.pdf', 100))
        // upload optional lab result
        ->set('labResultFile', UploadedFile::fake()->create('lab.pdf', 100))
        ->call('submit')
        ->assertHasNoErrors();

    // Ensure merger was called and lab result is included last
    expect($calledWithFiles)->not->toBeNull();
    expect($calledWithFiles)->toBeArray();
    expect(count($calledWithFiles ?? []))->toBeGreaterThanOrEqual(4);

    // Check ordering by filename suffix
    expect(Str::endsWith($calledWithFiles[0], 'sep.pdf'))->toBeTrue();
    $array = $calledWithFiles ?? [];
    $last = $array ? $array[count($array)-1] : '';
    expect(Str::endsWith($last, 'lab.pdf'))->toBeTrue();
});
