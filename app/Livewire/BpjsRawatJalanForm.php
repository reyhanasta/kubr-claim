<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Jobs\BackupFileJob;
use App\Models\BpjsClaim;
use App\Models\ClaimDocument;
use App\Services\GenerateFolderService;
use App\Services\PdfMergerService;
use App\Services\PdfReadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class BpjsRawatJalanForm extends Component
{
    use WithFileUploads;

    // File uploads
    #[Validate('required|file|mimes:pdf|max:2048')]
    public ?TemporaryUploadedFile $sepFile = null;

    #[Validate('nullable|file|mimes:pdf|max:2048')]
    public ?TemporaryUploadedFile $sepRJFile = null;

    #[Validate('required|file|mimes:pdf|max:2048')]
    public ?TemporaryUploadedFile $resumeFile = null;

    #[Validate('required|file|mimes:pdf,jpg,jpeg,png|max:2048')]
    public ?TemporaryUploadedFile $billingFile = null;

    // Lab result (optional, PDF only) – merged into final combined PDF
    #[Validate('nullable|file|mimes:pdf|max:2048')]
    public ?TemporaryUploadedFile $labResultFile = null;

    // Lab result (optional, PDF only) – merged into final combined PDF
    #[Validate('nullable|file|mimes:pdf|max:2048')]
    public ?TemporaryUploadedFile $labResultFile2 = null;

    #[Validate('nullable|file|mimes:pdf|max:2048')]
    public ?TemporaryUploadedFile $fileLIP = null;

    // Patient data
    #[Validate('required|string|max:50')]
    public string $medical_record_number = '';

    #[Validate('required|string|max:100')]
    public string $patient_name = '';

    #[Validate('required|string|max:50|unique:bpjs_claims,no_sep', message: 'Nomor SEP sudah terdaftar')]
    public string $sep_number = '';

    #[Validate('required|string|max:20')]
    public string $bpjs_serial_number = '';

    #[Validate('required|date')]
    public ?string $sep_date = null;

    #[Validate('required|string|in:1,2,3', message: 'Kelas rawatan harus berupa 1, 2, atau 3')]
    public string $patient_class = '';

    #[Locked]
    public string $jenis_rawatan = 'RJ';

    #[Locked]
    public string $sep_date_label = 'Tanggal SEP';

    // Internal state
    public array $scanned_docs = [];

    public array $rotatedPaths = [];

    public array $previewUrls = [];

    public bool $uploading = false;

    public bool $showUploadedData = false;

    // Constants
    private const ALLOWED_JENIS_RAWATAN = ['RJ', 'RI'];

    private const MAX_FILE_SIZE = 300; // KB

    private const TEMP_STORAGE_PATH = 'temp';

    private const RAW_DOCUMENTS_PATH = 'raw-documents';

    protected function messages(): array
    {
        return [
            'sepFile.required' => 'File SEP wajib diunggah',
            'sepFile.mimes' => 'File SEP harus berformat PDF maksimal 2MB',
            'sepFile.max' => 'File SEP maksimal 2MB',
            'resumeFile.required' => 'File Resume Medis wajib diunggah',
            'resumeFile.mimes' => 'File Resume Medis harus berformat PDF maksimal 2MB',
            'resumeFile.max' => 'File Resume Medis maksimal 2MB',
            'billingFile.required' => 'File Billing wajib diunggah',
            'billingFile.mimes' => 'File Billing harus berformat PDF/JPG/PNG maksimal 2MB',
            'billingFile.max' => 'File Billing maksimal 2MB',
            'fileLIP.mimes' => 'File LIP harus berformat PDF maksimal 2MB',
            'fileLIP.max' => 'File LIP maksimal 2MB',
            'sepRJFile.mimes' => 'File SEP RJ harus berformat PDF maksimal 2MB',
            'sepRJFile.max' => 'File SEP RJ maksimal 2MB',
            'labResultFile.mimes' => 'File Hasil Labor harus berformat PDF maksimal 2MB',
            'labResultFile.max' => 'File Hasil Labor maksimal 2MB',
            'labResultFile2.mimes' => 'File Hasil Labor harus berformat PDF maksimal 2MB',
            'labResultFile2.max' => 'File Hasil Labor maksimal 2MB',
            'sep_number.required' => 'Nomor SEP wajib diisi',
            'sep_number.unique' => 'Nomor SEP sudah terdaftar',
            'sep_date.required' => 'Tanggal SEP wajib diisi',
            'sep_date.date' => 'Tanggal SEP harus berupa format tanggal yang valid',
            'medical_record_number.required' => 'Nomor RM wajib diisi',
            'patient_name.required' => 'Nama pasien wajib diisi',
            'bpjs_serial_number.required' => 'Nomor kartu BPJS wajib diisi',
            'patient_class.required' => 'Kelas rawatan wajib diisi',
            'patient_class.in' => 'Kelas rawatan harus berupa 1, 2, atau 3',
        ];
    }

    #[On('cancelUploadTimeout')]
    public function handleUploadTimeout(): void
    {
        if (! $this->uploading) {
            return;
        }

        $this->cancelUpload();
        $this->showErrorAlert('Waktu pemrosesan terlalu lama!', 'File tidak dapat diproses, silakan coba lagi');
    }

    #[Computed]
    public function currentPreviewUrl(): string
    {
        return $this->previewUrls['sepFile'] ?? '';
    }

    #[Computed]
    public function requiredFilesUploaded(): bool
    {
        return $this->sepFile !== null
            && $this->resumeFile !== null
            && $this->billingFile !== null;
    }

    #[Computed]
    public function uploadProgress(): array
    {
        return [
            'sep' => $this->sepFile !== null,
            'resume' => $this->resumeFile !== null,
            'billing' => $this->billingFile !== null,
            'sepRJ' => $this->sepRJFile !== null,
            'lip' => $this->fileLIP !== null,
        ];
    }

    /**
     * Check if supporting documents form can be displayed.
     * For Rawat Inap (RI), user must fill discharge date first.
     */
    #[Computed]
    public function canShowSupportingDocuments(): bool
    {
        // For Rawat Jalan, always show after SEP is uploaded
        if ($this->jenis_rawatan === 'RJ') {
            return true;
        }

        // For Rawat Inap, require discharge date to be filled
        return ! empty($this->sep_date);
    }

    public function updatedSepFile(): void
    {
        if (! $this->sepFile) {
            return;
        }
       
        if ($this->sepFile->getSize() / 1024 > self::MAX_FILE_SIZE) {
            $this->showErrorAlert('Ukuran file terlalu besar', 'File SEP maksimal '.self::MAX_FILE_SIZE.' KB');
            $this->sepFile = null;

            return;
        }

        $this->uploading = true;

        try {
            $this->validateOnly('sepFile');
            $this->processSepFile();
        } catch (ValidationException $e) {
            $this->handleValidationErrors($e);
        } catch (\Throwable $e) {
            $this->handleFileProcessingError($e);
        } finally {
            $this->uploading = false;
        }
    }

    public function updatedResumeFile(): void
    {
        $this->processOptionalFile($this->resumeFile, 'resumeFile');
    }

    public function updatedSepRJFile(): void
    {
        $this->processOptionalFile($this->sepRJFile, 'sepRJFile');
    }

    public function updatedBillingFile(): void
    {
        $this->processOptionalFile($this->billingFile, 'billingFile');
    }

    public function updatedFileLIP(): void
    {
        $this->processOptionalFile($this->fileLIP, 'fileLIP');
    }

    public function updatedLabResultFile(): void
    {
        $this->processOptionalFile($this->labResultFile, 'labResultFile');
    }

    public function updatedLabResultFile2(): void
    {
        $this->processOptionalFile($this->labResultFile2, 'labResultFile2');
    }

    public function cancelUpload(): void
    {
        $this->uploading = false;
        $this->cleanupTempFiles();
        $this->resetUploadState();

        $this->showWarningAlert('Upload dibatalkan', 'Proses upload file dibatalkan');
    }

    public function cancelForm()
    {
        $this->cleanupTempFiles();
        $this->resetUploadState();

        $this->redirect(request()->header('Referer') ?? route('dashboard'), navigate: true);
    }

    public function submit(
        PdfMergerService $pdfMergeService,
        GenerateFolderService $generateFolderService
    ): void {
        // Validate all fields
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->handleValidationErrors($e);

            return;
        }

        // Double-check required files
        if (! $this->requiredFilesUploaded) {
            $this->showErrorAlert('File tidak lengkap', 'Semua file wajib harus diunggah sebelum menyimpan klaim');

            return;
        }

        DB::beginTransaction();

        try {
            // Generate output path
            $outputDir = $generateFolderService->generateOutputPath(
                $this->sep_date,
                $this->sep_number,
                $this->jenis_rawatan
            );

            // Prepare merged PDF path
            $patientNameSafe = $this->sanitizePatientName();
            $pdfOutputPath = $outputDir.$patientNameSafe.'.pdf';

            // Get ordered files for merging
            $orderedFiles = $this->getOrderedFilesForMerge();

            if (empty($orderedFiles)) {
                throw new \RuntimeException('Tidak ada file yang dapat digabungkan');
            }

            // Merge PDFs
            $finalPath = $pdfMergeService->mergePdfs($orderedFiles, $pdfOutputPath);

            // Create claim record with file path
            $claim = $this->createClaimRecord($finalPath);

            // Store claim documents
            $this->storeClaimDocuments($claim, $finalPath);

            // Handle optional LIP file
            $lipPath = $this->handleLipFile($claim, $outputDir);

            DB::commit();

            // Cleanup and dispatch backup job with claim ID for tracking
            $this->cleanUpAfterSubmit($pdfMergeService);
            BackupFileJob::dispatch($finalPath, $lipPath, $claim->id);

            $this->showSuccessAlert('Klaim berhasil dibuat!', 'Dokumen telah digabung dan disimpan');

            // Redirect to claims list or dashboard
            $this->redirect(route('bpjs-rajal-form'), navigate: true);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BPJS Claim Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'sep_number' => $this->sep_number,
            ]);

            $this->showErrorAlert('Klaim gagal dibuat!', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.bpjs-rawat-jalan-form');
    }

    // Private helper methods

    private function processSepFile(): void
    {
        /** @var PdfReadService $pdfReadService */
        $pdfReadService = app(PdfReadService::class);

        $pdfText = $pdfReadService->getPdfTextwithSpatie($this->sepFile);
        $extractedData = $pdfReadService->extractPdf($pdfText);

        if (! $extractedData) {
            throw new \RuntimeException('Format dokumen SEP tidak valid atau tidak dapat dibaca');
        }

        // Validate essential fields are not empty
        $this->validateExtractedData($extractedData);

        // Check if SEP number already exists in database
        $this->checkDuplicateSepNumber($extractedData['sep_number'] ?? '');

        $this->fillPatientData($extractedData);
        $this->storeTempFile($this->sepFile, 'sepFile');
        $this->showUploadedData = true;
    }

    /**
     * Check if SEP number already exists in database.
     *
     * @throws \RuntimeException if SEP number is duplicate
     */
    private function checkDuplicateSepNumber(string $sepNumber): void
    {
        if (empty($sepNumber)) {
            return;
        }

        $existingClaim = BpjsClaim::where('no_sep', $sepNumber)->first();

        if ($existingClaim) {
            $tanggalRawatan = $existingClaim->tanggal_rawatan?->format('d/m/Y') ?? '-';
            $jenisRawatan = $existingClaim->jenis_rawatan === 'RI' ? 'Rawat Inap' : 'Rawat Jalan';

            throw new \RuntimeException(
                "Nomor SEP {$sepNumber} sudah terdaftar sebelumnya. ".
                "Data klaim: {$existingClaim->nama_pasien} ({$jenisRawatan}) ".
                "tanggal {$tanggalRawatan}."
            );
        }
    }

    /**
     * Validate that essential data was extracted from SEP document.
     *
     * @throws \RuntimeException if essential data is missing
     */
    private function validateExtractedData(array $data): void
    {
        $requiredFields = [
            'sep_number' => 'Nomor SEP',
            'patient_name' => 'Nama Pasien',
            'medical_record_number' => 'Nomor Rekam Medis',
            'bpjs_serial_number' => 'Nomor Kartu BPJS',
        ];

        $missingFields = [];

        foreach ($requiredFields as $field => $label) {
            if (empty(trim($data[$field] ?? ''))) {
                $missingFields[] = $label;
            }
        }

        if (! empty($missingFields)) {
            throw new \RuntimeException(
                'Data SEP tidak lengkap. Field berikut tidak dapat dibaca: '.implode(', ', $missingFields).'. Pastikan file SEP yang diupload valid dan dapat dibaca.'
            );
        }
    }

    private function fillPatientData(array $data): void
    {
        // Extract numeric patient class from "Kelas 1", "Kelas 2", "Kelas 3"
        $patientClass = $data['patient_class'] ?? '';
        if (preg_match('/\d+/', $patientClass, $matches)) {
            $patientClass = $matches[0];
        }

        $this->fill([
            'medical_record_number' => $data['medical_record_number'] ?? '',
            'patient_name' => $data['patient_name'] ?? '',
            'sep_number' => $data['sep_number'] ?? '',
            'bpjs_serial_number' => $data['bpjs_serial_number'] ?? '',
            'patient_class' => $patientClass,
            'jenis_rawatan' => $data['jenis_rawatan'] ?? 'RJ',
            'sep_date' => $data['sep_date'] ?? null,
        ]);

        if ($this->jenis_rawatan === 'RI') {
            $this->sep_date_label = 'Tanggal Pulang';
            $this->sep_date = null;
        }
    }

    private function processOptionalFile(?TemporaryUploadedFile $file, string $key): void
    {
        if (! $file) {
            return;
        }

        try {
            $this->storeTempFile($file, $key);
        } catch (\Throwable $e) {
            Log::warning("Failed to process {$key}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function storeTempFile(TemporaryUploadedFile $file, string $key): void
    {
        $filename = $this->generateUniqueFilename($file);
        $storedPath = $file->storeAs(self::TEMP_STORAGE_PATH, $filename, 'public');

        $this->scanned_docs[$key] = $file;
        $this->rotatedPaths[$key] = $storedPath;
        $this->previewUrls[$key] = $key === 'sepFile'
            ? url('storage/'.$storedPath)
            : Storage::url($storedPath);

        Log::info("File {$key} uploaded", compact('filename', 'storedPath'));
    }

    private function generateUniqueFilename(TemporaryUploadedFile $file): string
    {
        return uniqid('', true).'_'.$file->getClientOriginalName();
    }

    private function sanitizePatientName(): string
    {
        return Str::of($this->patient_name)
            ->upper()
            ->ascii()
            ->replaceMatches('/[^A-Z0-9_\-]/', '_')
            ->toString();
    }

    private function getOrderedFilesForMerge(): array
    {
        // Urutan: SEP → SEP RJ → Resume → Lab Results → Billing (terakhir)
        return collect([
            $this->rotatedPaths['sepFile'] ?? null,
            $this->rotatedPaths['sepRJFile'] ?? null,
            $this->rotatedPaths['resumeFile'] ?? null,
            $this->rotatedPaths['labResultFile'] ?? null,
            $this->rotatedPaths['labResultFile2'] ?? null,
            $this->rotatedPaths['billingFile'] ?? null,
        ])->filter()->values()->all();
    }

    private function createClaimRecord(string $finalPath): BpjsClaim
    {
        return BpjsClaim::create([
            'no_rm' => $this->medical_record_number,
            'no_kartu_bpjs' => $this->bpjs_serial_number,
            'no_sep' => $this->sep_number,
            'jenis_rawatan' => $this->jenis_rawatan,
            'tanggal_rawatan' => $this->sep_date,
            'nama_pasien' => $this->patient_name,
            'kelas_rawatan' => $this->patient_class,
            'file_path' => $finalPath,
        ]);
    }

    private function storeClaimDocuments(BpjsClaim $claim, string $finalPath): void
    {
        Storage::disk('public')->makeDirectory(self::RAW_DOCUMENTS_PATH);

        foreach ($this->scanned_docs as $index => $file) {
            if (! $file || $index === 'fileLIP') {
                continue;
            }

            $filename = $this->generateUniqueFilename($file);
            $this->moveToRawDocuments($index, $file, $filename);

            ClaimDocument::create([
                'bpjs_claims_id' => $claim->id,
                'filename' => $filename,
                'order' => $index,
                'disk' => Storage::disk('shared')->path($finalPath),
            ]);
        }
    }

    private function moveToRawDocuments(string $index, TemporaryUploadedFile $file, string $filename): void
    {
        $tempPath = $this->rotatedPaths[$index] ?? null;
        $destination = self::RAW_DOCUMENTS_PATH.'/'.$filename;

        if ($tempPath && Storage::disk('public')->exists($tempPath)) {
            Storage::disk('public')->move($tempPath, $destination);
        } else {
            $file->storeAs(self::RAW_DOCUMENTS_PATH, $filename, 'public');
        }
    }

    private function handleLipFile(BpjsClaim $claim, string $outputDir): ?string
    {
        if (! $this->fileLIP) {
            return null;
        }

        $lipFilename = 'LIP.pdf';
        $lipPath = $outputDir.$lipFilename;

        Storage::disk('shared')->putFileAs($outputDir, $this->fileLIP, $lipFilename);

        // Update claim record with LIP path
        $claim->update(['lip_file_path' => $lipPath]);

        Log::info('LIP file saved', compact('lipPath'));

        return $lipPath;
    }

    private function cleanupTempFiles(): void
    {
        foreach ($this->rotatedPaths as $path) {
            if (str_starts_with($path, self::TEMP_STORAGE_PATH.'/') && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function cleanUpAfterSubmit(PdfMergerService $pdfMergeService): void
    {
        $this->cleanupTempFiles();
        $pdfMergeService->cleanupTempFiles($this->rotatedPaths);
        $this->resetUploadState();
    }

    private function resetUploadState(): void
    {
        $this->reset([
            'sepFile',
            'sepRJFile',
            'resumeFile',
            'billingFile',
            'fileLIP',
            'labResultFile',
            'labResultFile2',
            'scanned_docs',
            'previewUrls',
            'rotatedPaths',
            'showUploadedData',
        ]);
    }

    private function handleValidationErrors(ValidationException $e): void
    {
        $this->uploading = false;

        foreach ($e->validator->errors()->all() as $error) {
            LivewireAlert::toast()
                ->error()
                ->title('Validasi Gagal')
                ->text($error)
                ->position('top-end')
                ->timer(4000)
                ->show();
        }
    }

    private function handleFileProcessingError(\Throwable $e): void
    {
        Log::error('File processing error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->cancelUpload();

        // Use custom message from RuntimeException, otherwise use generic message
        if ($e instanceof \RuntimeException) {
            $this->showErrorAlert('Gagal memproses file!', $e->getMessage());
        } else {
            $this->showErrorAlert('Gagal memproses file!', 'Terjadi kesalahan saat memproses file');
        }
    }

    private function showSuccessAlert(string $title, string $text): void
    {
        LivewireAlert::title($title)
            ->success()
            ->text($text)
            ->show();
    }

    private function showErrorAlert(string $title, string $text): void
    {
        LivewireAlert::title($title)
            ->error()
            ->text($text)
            ->timer(5000)
            ->show();
    }

    private function showWarningAlert(string $title, string $text): void
    {
        LivewireAlert::toast()
            ->warning()
            ->title($title)
            ->text($text)
            ->position('top-end')
            ->timer(3000)
            ->show();
    }
}
