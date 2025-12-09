<?php

use App\Models\BpjsClaim;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('shared');

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('user can view bpjs claim form', function () {
    $response = $this->get(route('bpjs-claim-form'));

    $response->assertStatus(200);
    $response->assertSee('Formulir Klaim BPJS');
});

test('user can search for existing patient', function () {
    Patient::create([
        'no_rkm_medis' => 'RM123456',
        'nm_pasien' => 'John Doe',
        'no_peserta' => '0001234567890',
    ]);

    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('no_rm', 'RM123456')
        ->call('searchPatient')
        ->assertSet('patient_name', 'John Doe')
        ->assertSet('no_kartu_bpjs', '0001234567890')
        ->assertSet('rmIcon', 'check-circle');
});

test('user sees error when searching non-existent patient', function () {
    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('no_rm', 'INVALID')
        ->call('searchPatient')
        ->assertSet('patient_name', '')
        ->assertSet('no_kartu_bpjs', '')
        ->assertSet('rmIcon', 'x-circle');
});

test('user can upload documents', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('scanned_docs', [$file])
        ->assertHasNoErrors();
});

test('user cannot upload files larger than 2MB', function () {
    $file = UploadedFile::fake()->create('large.pdf', 3000, 'application/pdf');

    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('scanned_docs', [$file])
        ->call('updatedScannedDocs')
        ->assertHasErrors(['scanned_docs.*' => 'max']);
});

test('user can rotate uploaded file', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('scanned_docs', [$file])
        ->call('rotateFile', 0)
        ->assertSet('rotations.0', 90);
});

test('user can reorder files by moving up', function () {
    $file1 = UploadedFile::fake()->create('doc1.pdf', 1000);
    $file2 = UploadedFile::fake()->create('doc2.pdf', 1000);

    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('scanned_docs', [$file1, $file2])
        ->call('moveUp', 1)
        ->assertCount('scanned_docs', 2);
});

test('user can remove uploaded file', function () {
    $file1 = UploadedFile::fake()->create('doc1.pdf', 1000);
    $file2 = UploadedFile::fake()->create('doc2.pdf', 1000);

    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('scanned_docs', [$file1, $file2])
        ->call('removeFile', 0)
        ->assertCount('scanned_docs', 1);
});

test('user can clear all files', function () {
    $file1 = UploadedFile::fake()->create('doc1.pdf', 1000);
    $file2 = UploadedFile::fake()->create('doc2.pdf', 1000);

    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('scanned_docs', [$file1, $file2])
        ->call('clearAllFiles')
        ->assertCount('scanned_docs', 0);
});

test('claim creation requires all fields', function () {
    Livewire::test(\App\Livewire\BpjsClaimFormRefactored::class)
        ->set('no_rm', '')
        ->set('tanggal_rawatan', '')
        ->set('jenis_rawatan', '')
        ->set('no_sep', '')
        ->call('submit')
        ->assertHasErrors(['no_rm', 'tanggal_rawatan', 'jenis_rawatan', 'no_sep']);
});

test('bpjs claim has relationship with patient', function () {
    $patient = Patient::create([
        'no_rkm_medis' => 'RM123',
        'nm_pasien' => 'Test Patient',
        'no_peserta' => '0001234567890',
    ]);

    $claim = BpjsClaim::create([
        'no_rkm_medis' => 'RM123',
        'no_kartu_bpjs' => '0001234567890',
        'no_sep' => 'SEP123',
        'jenis_rawatan' => 'RJ',
        'tanggal_rawatan' => now(),
        'nama_pasien' => 'Test Patient',
    ]);

    expect($claim->patient)->not->toBeNull();
    expect($claim->patient->no_rkm_medis)->toBe('RM123');
});

test('bpjs claim can have multiple documents', function () {
    $claim = BpjsClaim::create([
        'no_rkm_medis' => 'RM123',
        'no_kartu_bpjs' => '0001234567890',
        'no_sep' => 'SEP123',
        'jenis_rawatan' => 'RJ',
        'tanggal_rawatan' => now(),
        'nama_pasien' => 'Test Patient',
    ]);

    $claim->documents()->create([
        'filename' => 'doc1.pdf',
        'order' => 0,
        'disk' => '/path/to/doc1.pdf',
    ]);

    $claim->documents()->create([
        'filename' => 'doc2.pdf',
        'order' => 1,
        'disk' => '/path/to/doc2.pdf',
    ]);

    expect($claim->documents)->toHaveCount(2);
});

test('bpjs claim can be filtered by month', function () {
    BpjsClaim::create([
        'no_rkm_medis' => 'RM123',
        'no_kartu_bpjs' => '0001234567890',
        'no_sep' => 'SEP123',
        'jenis_rawatan' => 'RJ',
        'tanggal_rawatan' => now(),
        'nama_pasien' => 'Test Patient 1',
    ]);

    BpjsClaim::create([
        'no_rkm_medis' => 'RM124',
        'no_kartu_bpjs' => '0001234567891',
        'no_sep' => 'SEP124',
        'jenis_rawatan' => 'RI',
        'tanggal_rawatan' => now()->subMonth(),
        'nama_pasien' => 'Test Patient 2',
    ]);

    $currentMonthClaims = BpjsClaim::forMonth(now()->month, now()->year)->get();

    expect($currentMonthClaims)->toHaveCount(1);
    expect($currentMonthClaims->first()->no_sep)->toBe('SEP123');
});

test('bpjs claim can be filtered by rawat jalan', function () {
    BpjsClaim::create([
        'no_rkm_medis' => 'RM123',
        'no_kartu_bpjs' => '0001234567890',
        'no_sep' => 'SEP123',
        'jenis_rawatan' => 'RJ',
        'tanggal_rawatan' => now(),
        'nama_pasien' => 'RJ Patient',
    ]);

    BpjsClaim::create([
        'no_rkm_medis' => 'RM124',
        'no_kartu_bpjs' => '0001234567891',
        'no_sep' => 'SEP124',
        'jenis_rawatan' => 'RI',
        'tanggal_rawatan' => now(),
        'nama_pasien' => 'RI Patient',
    ]);

    $rjClaims = BpjsClaim::rawatJalan()->get();

    expect($rjClaims)->toHaveCount(1);
    expect($rjClaims->first()->jenis_rawatan)->toBe('RJ');
});
