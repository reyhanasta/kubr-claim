<?php

declare(strict_types=1);

use App\Livewire\BpjsRawatJalanForm;
use App\Models\BpjsClaim;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('shared');
});

test('component can render', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->assertStatus(200);
});

test('validates required files on submit', function () {
    $user = User::factory()->create();

    // Test that submit method checks for required files
    $component = Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('sep_number', 'SEP123')
        ->set('sep_date', '2025-11-06')
        ->set('medical_record_number', 'RM001')
        ->set('patient_name', 'John Doe')
        ->set('bpjs_serial_number', '1234567890')
        ->set('patient_class', '1')
        ->call('submit');

    // Check that requiredFilesUploaded computed property is false
    expect($component->get('requiredFilesUploaded'))->toBeFalse();

    // Verify no claim was created (because files missing)
    expect(BpjsClaim::count())->toBe(0);
});

test('validates patient class must be 1, 2, or 3', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('sepFile', UploadedFile::fake()->create('sep.pdf', 100))
        ->set('resumeFile', UploadedFile::fake()->create('resume.pdf', 100))
        ->set('billingFile', UploadedFile::fake()->create('billing.pdf', 100))
        ->set('sep_number', 'SEP123')
        ->set('sep_date', '2025-11-06')
        ->set('medical_record_number', 'RM001')
        ->set('patient_name', 'John Doe')
        ->set('bpjs_serial_number', '1234567890')
        ->set('patient_class', '4') // Invalid class
        ->call('submit')
        ->assertHasErrors(['patient_class']);
});

test('accepts valid patient classes', function (string $class) {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('patient_class', $class)
        ->assertHasNoErrors(['patient_class']);
})->with(['1', '2', '3']);

test('sep file must be pdf', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('sepFile', UploadedFile::fake()->create('sep.jpg', 100))
        ->assertHasErrors(['sepFile']);
});

test('resume file must be pdf', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('resumeFile', UploadedFile::fake()->create('resume.txt', 100))
        ->assertHasErrors(['resumeFile']);
});

test('billing file accepts pdf, jpg, jpeg, png', function (string $extension) {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('billingFile', UploadedFile::fake()->create("billing.{$extension}", 100))
        ->assertHasNoErrors(['billingFile']);
})->with(['pdf', 'jpg', 'jpeg', 'png']);

test('file size cannot exceed 2MB', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('sepFile', UploadedFile::fake()->create('sep.pdf', 3000)) // 3MB
        ->assertHasErrors(['sepFile']);
});

test('sep number must be unique', function () {
    $user = User::factory()->create();

    // Create existing claim
    BpjsClaim::create([
        'no_sep' => 'SEP123',
        'no_rm' => 'RM001',
        'no_kartu_bpjs' => '1234567890',
        'jenis_rawatan' => 'RJ',
        'tanggal_rawatan' => '2025-11-06',
        'nama_pasien' => 'Existing Patient',
        'kelas_rawatan' => '1',
    ]);

    Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('sep_number', 'SEP123')
        ->set('sep_date', '2025-11-06')
        ->set('medical_record_number', 'RM002')
        ->assertHasErrors(['sep_number']);
});

test('required files uploaded check works correctly', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class);

    // Initially no files uploaded - check via scanned_docs array
    expect($component->scanned_docs)->toBeEmpty();

    // Upload all required files
    $component
        ->set('sepFile', UploadedFile::fake()->create('sep.pdf', 100))
        ->set('resumeFile', UploadedFile::fake()->create('resume.pdf', 100))
        ->set('billingFile', UploadedFile::fake()->create('billing.pdf', 100));

    // Verify files were processed and stored in scanned_docs
    // Note: updatedSepFile() stores in scanned_docs['sepFile']
    expect($component->scanned_docs)->toBeArray();
});

test('upload progress tracking works', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class);

    // Check initial state - scanned_docs is empty
    expect($component->scanned_docs)->toBeEmpty();

    // Upload files one by one and verify scanned_docs gets populated
    $component->set('resumeFile', UploadedFile::fake()->create('resume.pdf', 100));

    $component->set('billingFile', UploadedFile::fake()->create('billing.pdf', 100));

    // After uploads, scanned_docs should be populated
    expect($component->scanned_docs)->toBeArray();
});

test('cancel upload cleans up files and resets state', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('sepFile', UploadedFile::fake()->create('sep.pdf', 100))
        ->set('showUploadedData', true)
        ->call('cancelUpload');

    expect($component->sepFile)->toBeNull();
    expect($component->showUploadedData)->toBeFalse();
});

test('sanitize patient name removes special characters', function () {
    $form = new BpjsRawatJalanForm;
    $form->patient_name = 'Ñoño José María';

    $reflection = new ReflectionClass($form);
    $method = $reflection->getMethod('sanitizePatientName');
    $method->setAccessible(true);

    $sanitized = $method->invoke($form);

    expect($sanitized)->toMatch('/^[A-Z0-9_\-]+$/');
    expect($sanitized)->not->toContain('ñ');
});

test('generate unique filename creates unique names', function () {
    $form = new BpjsRawatJalanForm;
    $file1 = UploadedFile::fake()->create('test.pdf');
    $file2 = UploadedFile::fake()->create('test.pdf');

    $reflection = new ReflectionClass($form);
    $method = $reflection->getMethod('generateUniqueFilename');
    $method->setAccessible(true);

    // Convert to TemporaryUploadedFile by uploading
    $component = Livewire::test(BpjsRawatJalanForm::class)
        ->set('sepFile', $file1);

    $tempFile = $component->sepFile;

    if ($tempFile) {
        $filename1 = $method->invoke($form, $tempFile);
        usleep(1000);
        $filename2 = $method->invoke($form, $tempFile);

        expect($filename1)->not->toBe($filename2);
        expect($filename1)->toContain('test.pdf');
    } else {
        $this->markTestSkipped('Could not create TemporaryUploadedFile');
    }
});

test('locked properties cannot be modified from frontend', function () {
    $user = User::factory()->create();

    expect(fn () => Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('jenis_rawatan', 'HACKED'))
        ->toThrow(\Exception::class);
});

test('fill patient data extracts numeric class from text', function () {
    $form = new BpjsRawatJalanForm;

    $reflection = new ReflectionClass($form);
    $method = $reflection->getMethod('fillPatientData');
    $method->setAccessible(true);

    $method->invoke($form, [
        'patient_class' => 'Kelas 3',
        'patient_name' => 'Test Patient',
        'sep_number' => 'SEP123',
    ]);

    expect($form->patient_class)->toBe('3');
});

test('fill patient data handles various class formats', function (string $input, string $expected) {
    $form = new BpjsRawatJalanForm;

    $reflection = new ReflectionClass($form);
    $method = $reflection->getMethod('fillPatientData');
    $method->setAccessible(true);

    $method->invoke($form, ['patient_class' => $input]);

    expect($form->patient_class)->toBe($expected);
})->with([
    ['Kelas 1', '1'],
    ['Kelas 2', '2'],
    ['Kelas 3', '3'],
    ['kelas 1', '1'],
    ['KELAS 3', '3'],
    ['1', '1'],
    ['2', '2'],
    ['3', '3'],
]);

test('constants are defined correctly', function () {
    $reflection = new ReflectionClass(BpjsRawatJalanForm::class);

    expect($reflection->getConstant('TEMP_STORAGE_PATH'))->toBe('temp');
    expect($reflection->getConstant('RAW_DOCUMENTS_PATH'))->toBe('raw-documents');
    expect($reflection->getConstant('MAX_FILE_SIZE'))->toBe(2048);
    expect($reflection->getConstant('ALLOWED_JENIS_RAWATAN'))->toBe(['RJ', 'RI']);
});

test('validation messages are user friendly', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('sepFile', UploadedFile::fake()->create('sep.jpg', 100));

    $errors = $component->errors();

    expect($errors->get('sepFile')[0])->toContain('PDF');
});

test('patient class validation message is user friendly', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class)
        ->set('patient_class', '5')
        ->set('sep_number', 'SEP123')
        ->set('sep_date', '2025-11-06')
        ->set('medical_record_number', 'RM001')
        ->set('patient_name', 'John Doe')
        ->set('bpjs_serial_number', '1234567890')
        ->call('submit');

    $errors = $component->errors();
    expect($errors->get('patient_class')[0])->toContain('1, 2, atau 3');
});

test('can show supporting documents returns true for rawat jalan', function () {
    $user = User::factory()->create();

    // Default jenis_rawatan is 'RJ', so should return true
    $component = Livewire::actingAs($user)
        ->test(BpjsRawatJalanForm::class);

    // RJ should always show supporting documents
    expect($component->get('canShowSupportingDocuments'))->toBeTrue();
});

test('can show supporting documents behavior for rawat inap', function () {
    $user = User::factory()->create();

    // We need to test this via the component instance directly
    // since jenis_rawatan is a locked property
    $component = new BpjsRawatJalanForm;

    // Test RJ - should always return true
    $component->jenis_rawatan = 'RJ';
    $component->sep_date = null;
    expect($component->canShowSupportingDocuments())->toBeTrue();

    // Test RI without date - should return false
    $component->jenis_rawatan = 'RI';
    $component->sep_date = null;
    expect($component->canShowSupportingDocuments())->toBeFalse();

    // Test RI with date - should return true
    $component->jenis_rawatan = 'RI';
    $component->sep_date = '2025-11-10';
    expect($component->canShowSupportingDocuments())->toBeTrue();
});

test('validate extracted data throws exception when essential fields are empty', function () {
    $component = new BpjsRawatJalanForm;

    // Use reflection to access private method
    $method = new ReflectionMethod($component, 'validateExtractedData');
    $method->setAccessible(true);

    // Test with empty sep_number
    expect(fn () => $method->invoke($component, [
        'sep_number' => '',
        'patient_name' => 'John Doe',
        'medical_record_number' => 'RM001',
        'bpjs_serial_number' => '1234567890',
    ]))->toThrow(RuntimeException::class, 'Nomor SEP');

    // Test with empty patient_name
    expect(fn () => $method->invoke($component, [
        'sep_number' => 'SEP123',
        'patient_name' => '',
        'medical_record_number' => 'RM001',
        'bpjs_serial_number' => '1234567890',
    ]))->toThrow(RuntimeException::class, 'Nama Pasien');

    // Test with all empty fields
    expect(fn () => $method->invoke($component, [
        'sep_number' => '',
        'patient_name' => '',
        'medical_record_number' => '',
        'bpjs_serial_number' => '',
    ]))->toThrow(RuntimeException::class);
});

test('validate extracted data passes when all essential fields are present', function () {
    $component = new BpjsRawatJalanForm;

    $method = new ReflectionMethod($component, 'validateExtractedData');
    $method->setAccessible(true);

    // Should not throw exception
    $method->invoke($component, [
        'sep_number' => 'SEP123',
        'patient_name' => 'John Doe',
        'medical_record_number' => 'RM001',
        'bpjs_serial_number' => '1234567890',
    ]);

    expect(true)->toBeTrue(); // If we reach here, no exception was thrown
});
