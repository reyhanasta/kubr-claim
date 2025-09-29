<?php

namespace App\Livewire;

use App\Models\Patient;
use Livewire\Component;
use App\Models\BpjsClaim;
use Illuminate\Support\Str;
use App\Models\ClaimDocument;
use Livewire\WithFileUploads;
use App\Services\PdfReadService;
use App\Services\PdfMergerService;
use CzProject\PdfRotate\PdfRotate;
use Illuminate\Support\Facades\Log;
use App\Services\GenerateFolderService;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BpjsRawatJalanForm extends Component
{
    use WithFileUploads;
    /**
     * Summary of scanned_docs
     * @var array
     */
    public $scanned_docs = ['sepFile'=>'','resumeFile'=>'','billingFile'=>'']; // For scanned documents
    public $new_docs = []; // For new file uploads
    public $rotatedPaths = [];
    public $previewUrls = [];
    public $sepFile; // For SEP file upload
    public $resumeFile; // For resume file upload
    public $billingFile; // For billing file upload
    public $uploading = false;
    public $progress = 0;
    public $isProcessing = false;

    /**
     * Summary of medical records
     * @var 
     */
    public $patient_name; 
    public $sep_date;
    public $sep_number;
    public $bpjs_serial_number;
    public $medical_record_number;
    public $confirmPatient = false;
    public $patientValidated = false;
    public $simrs_rm_number = '';
    public $simrs_patient_name = '';
    public $simrs_bpjs_serial_number = '';
    public $treatment = 'RJ'; // Default to 'RAWAT JALAN'
    public $pdfText;
    public $showPreviewModal = false;
    public $showUploadedData = false;
    public $currentPreviewIndex = null;
    public $rmIcon = 'magnifying-glass';
    public $rotations = [];

    protected $rules = [
        'scanned_docs.*' => 'required|file|mimes:pdf|max:2048' // 2MB max
    ];

     protected $messages = [
            'medical_record_number.required' => 'Nomor RM wajib diisi.',
            'medical_record_number.exists' => 'Nomor RM tidak ditemukan.',
            'sep_date.required' => 'Tanggal rawatan wajib diisi.',
            'sep_date.date' => 'Tanggal rawatan harus berupa tanggal.',
            'jenis_rawatan.required' => 'Jenis rawatan wajib diisi.',
            'sep_number.required' => 'Nomor SEP wajib diisi.',
            'scanned_docs.*.required' => 'File wajib diisi.',
            'scanned_docs.*.file' => 'File harus berformat PDF, JPG, atau PNG.',
            'scanned_docs.*.mimes' => 'File harus berformat PDF, JPG, atau PNG.',
            'scanned_docs.*.max' => 'File tidak boleh lebih dari 2MB.',
    ];
    

    protected $listeners = [
        'cancelUploadTimeout' => 'handleUploadTimeout'
    ];

    public function handleUploadTimeout()
    {
        if ($this->uploading) {
            $this->cancelUpload();
            LivewireAlert::title('Waktu pemrosesan terlalu lama!')
                ->error()
                ->text('File tidak dapat diproses, silakan coba lagi')
                ->timer(5000)
                ->show();
        }
    }

    public function confirmPatientTrue(){
        Log::info('confirmPatient: Processing...');
        $this->confirmPatient = true;
    }

    /* ====================
       PREVIEW METHODS
       ==================== */
    public function getCurrentPreviewUrlProperty()
    {
        if ($this->currentPreviewIndex !== null && isset($this->previewUrls[$this->currentPreviewIndex])) {
            return $this->previewUrls[$this->currentPreviewIndex];
        }
        return '';
    }

    public function previewFile($index)
    {
        if (isset($this->scanned_docs[$index])) {
            $this->currentPreviewIndex = $index;
            $this->showPreviewModal = true;
        }
    }

    public function closePreviewModal()
    {
        $this->showPreviewModal = false;
        $this->currentPreviewIndex = null;
    }

    public function updateUploadedDocuments(){
        $this->validateOnly('scanned_docs.*');
        Log::info('updatedScannedDocs: Mulai memproses...');
        Log::debug('updatedScannedDocs: Dokumen yang akan diproses:', [
            'count' => count($this->scanned_docs),
            'files' => array_map(fn($doc) => $doc->getClientOriginalName(), $this->scanned_docs)
        ]);
        $currentRotationsState = $this->rotations;

        // 2. Bersihkan file fisik LAMA yang sebelumnya ada di $this->rotatedPaths.
        //    Ini penting karena kita akan membuat file temporer baru dan menghindari penumpukan.
        Log::debug('updatedScannedDocs: Membersihkan rotatedPaths lama...', ['old_paths' => $this->rotatedPaths]);
        foreach ($this->rotatedPaths as $oldRelativePath) {
            if ($oldRelativePath && Storage::disk('public')->exists($oldRelativePath)) {
                Storage::disk('public')->delete($oldRelativePath);
                Log::info("updatedScannedDocs: Menghapus file lama: {$oldRelativePath}");
            }
        }

        // 3. Reset properti yang akan dibangun ulang.
        //    $this->rotations akan dibangun ulang untuk memastikan sinkronisasi sempurna dengan $scanned_docs.
        
      
        // $this->previewUrls = [];
       
        foreach ($this->scanned_docs as $index => $doc) {
            $originalClientFilename = $doc->getClientOriginalName() ?? 'unknown_file'; // Handle jika null
            $filename = Str::uuid()->toString() . '_' . $originalClientFilename;
            $storedPath = $doc->storeAs('temp', $filename, 'public'); // Ini menyimpan file fisik
          
            Log::info("updatedScannedDocs: File [{$index}] '{$originalClientFilename}' disimpan ke '{$storedPath}'.");

            // B. Tentukan status rotasi untuk dokumen ini.
            //    Ambil dari $currentRotationsState yang sudah memiliki urutan yang benar.
            $rotationForThisDoc = $currentRotationsState[$index] ?? 0;
            $this->rotations[$index] = $rotationForThisDoc; // Bangun ulang $this->rotations dengan benar

            // Rotate PDF if needed
            // $rotation = $this->rotations[$index] ?? 0;
            // if ($rotation !== 0) {
            //     $this->rotatePdf($fullPath, $rotation);
            // }
    
             // Simpan untuk preview dan merge
            $this->rotatedPaths[] = $storedPath;
            $this->previewUrls[$index] = Storage::url($storedPath);
        }
            Log::debug('scanned_docs', ['scanned_docs' => $this->scanned_docs,'scanned_docs_count' => count($this->scanned_docs)]);
            Log::debug('sepFile', ['sepFile' => $this->sepFile]);
            Log::debug('scannedDocs', ['scannedDocs' => $this->scanned_docs['sepFile']]);
            Log::info('updatedScannedDocs: Proses selesai. Rotated paths:', ['rotatedPaths' => $this->rotatedPaths]);
    }
    /* ====================
       PDF READ METHODS
       ==================== */
       public function readPdfFile($pdfReadService) {
            Log::info('updatedSepFile: Processing...');
            Log::info('Processing scanned documents...');
            $this->pdfText = $pdfReadService->getPdfTextwithSpatie($this->sepFile);
            $data = $pdfReadService->extractPdf($this->pdfText);
            switch($data == null){
                case true:
                LivewireAlert::title('Format dokumen salah!')
                ->error()
                ->text('Silahkan upload ulang file SEP')
                ->timer(10000) // Dismisses after 10 seconds
                ->show();
                break;
                case false:
                $this->fill($data);
                $this->simrs_rm_number = $this->medical_record_number;
                Log::info('PDF text extracted successfully.', [
                    'sep_number' => $this->sep_number,
                    'bpjs_serial_number' => $this->bpjs_serial_number,
                    'medical_record_number' => $this->medical_record_number,
                    'patient_name' => $this->patient_name,
                ]);
                $this->showUploadedData = true;
                // $this->searchPatient();
                break;
            }
           
       }
       
       public function validatePatientData(){
            $patientContaint = Str::contains($this->simrs_patient_name, $this->patient_name);
            $medicalRecord = Str::contains($this->simrs_rm_number, $this->medical_record_number);
            switch($patientContaint && $medicalRecord){
                case true:
                    $this->patientValidated = true;
                    break;
                case false:
                    $this->patientValidated = false;
            }
       }
    /* ====================
       FILE UPLOAD METHODS
       ==================== */
    public function updatedSepFile(PdfReadService $pdfReadService)
    {
        try {
            $this->uploading = true;
            $this->validateOnly('sepFile'); // Validasi file SEP
            $this->readPdfFile($pdfReadService); // Baca file PDF SEP
            
            // Process the SEP file for preview
            if ($this->sepFile) {
                $filename = uniqid() . '_' . $this->sepFile->getClientOriginalName();
                $storedPath = $this->sepFile->storeAs('temp', $filename, 'public');
                
                // Store preview URL
                $this->previewUrls[0] = Storage::url($storedPath);
                
                // Add to scanned docs
                $this->scanned_docs['sepFile'] = $this->sepFile;
                $this->rotatedPaths[0] = $storedPath;
                
                Log::info('SEP File processed for preview', [
                    'filename' => $filename,
                    'stored_path' => $storedPath
                ]);
            }
        } catch (\Exception $e) {
            Log::error('File processing error: ' . $e->getMessage());
            $this->cancelUpload();
            LivewireAlert::title('Gagal memproses file!')
                ->error()
                ->text('Terjadi kesalahan saat memproses file')
                ->timer(5000)
                ->show();
        } finally {
            $this->uploading = false;
        }
    }
    public function updatedResumeFile()
    {
        Log::info('resumeFile to scannedDocs: Processing...');
        $this->processingDocuments('resumeFile',$this->resumeFile); // Proses dokumen billing
        
    }
    public function updatedBillingFile()
    {
        Log::info('updatedSepFile: Processing...');
        $this->processingDocuments('billingFile',$this->billingFile); // Proses dokumen billing
    }
    protected function processingDocuments($index, $file){
        $this->scanned_docs[$index] = $file; // Tambahkan SEP file ke scanned_docs
        Log::debug('updatedSepFile: Billing file added to scanned_docs.', [
            'file_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
        ]);
         Log::info('updatedNewDocs: Dokumen baru telah ditambahkan ke scanned_docs.', [
            'total_docs' => count($this->scanned_docs),
        ]);
        $this->updateUploadedDocuments();
        Log::info('updatedNewDocs: Dokumen baru diproses dan ditambahkan ke scanned_docs.',$this->scanned_docs);

    }

    public function cancelForm(){
        Log::info('resetAll: Resetting all data...');
        $this->reset(
            'scanned_docs',
            'new_docs',
            'rotatedPaths',
            'previewUrls'
        );
        return redirect(request()->header('Referer'));
    }

    public function cancelUpload()
    {
        Log::info('cancelUpload: Canceling file upload...');
        $this->uploading = false;
        
        // Reset file upload related properties
        $this->reset([
            'sepFile',
            'resumeFile',
            'billingFile',
            'scanned_docs',
            'previewUrls',
            'rotatedPaths',
            'showUploadedData'
        ]);

        // Clean up any temporary files
        foreach ($this->rotatedPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info("cancelUpload: Deleted temporary file: {$path}");
            }
        }

        LivewireAlert::toast()
            ->warning()
            ->title('Upload dibatalkan')
            ->text('Proses upload file dibatalkan')
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    /* ====================
       SUBMIT METHODS
       ==================== */
    public function submit(PdfMergerService $pdfMergeService, GenerateFolderService $generateFolderService)
    {
        $this->validate();

        try {
            $outputPath = $generateFolderService->generateOutputPath($this->sep_date,$this->sep_number,$this->patient_name);
            // PERBAIKAN 13: Perbaikan logika merge files dan pastikan direktori tujuan ada
            // Prepare final PDF output path
            // Storage::disk('public')->makeDirectory($outputPath);
            
            // Use rotatedPaths yang sudah diproses sebelumnya
            if (empty($this->rotatedPaths)) {
                throw new \Exception("No files available to merge");
            }
            // Step 3: Merge all PDFs
            $finalPath = $pdfMergeService->mergePdfs($this->rotatedPaths, $outputPath);

            // Step 4: Save claim data
            $claim = $this->createClaimRecord();

            // PERBAIKAN 16: Simpan ke shared disk
            $this->storeClaimDocuments($claim,$finalPath);

            // Step 6: Clean up temp files
            $this->cleanUpAfterSubmit($pdfMergeService);

            Log::info('submit: Klaim berhasil dibuat!',['showUploadedData' => $this->showUploadedData]);

            // LivewireAlert::title('Klaim berhasil dibuat!')
            //         ->text('Apakah ingin menambahkan klaim LIP ?')
            //         ->confirm()
            //         ->onConfirm('inputLIP')
            //         ->show();
            LivewireAlert::title('Klaim berhasil dibuat!')
                    ->text('Folder berhasil dibuat di shared drive')
                    ->success()
                    ->show();

        } catch (\Exception $e) {
            Log::error("BPJS Claim Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            LivewireAlert::title('Klaim gagal dibuat!')
                ->error()
                ->text('Terjadi kegagalan saat penyimpanan file: ' . $e->getMessage())
                ->timer(2400)
                ->show();
        }
    }
    public function inputLIP(){
        return redirect()->route('bpjs-rajal-lip');
    }

    protected function cleanUpAfterSubmit($pdfMergeService){
        $pdfMergeService->cleanupTempFiles($this->rotatedPaths);
        $this->reset();
    }
    protected function createClaimRecord(): BpjsClaim
    {
        return BpjsClaim::create([
            'no_rkm_medis' => $this->medical_record_number,
            'no_kartu_bpjs' => $this->bpjs_serial_number,
            'no_sep' => $this->sep_number,
            'jenis_rawatan' => 'RJ', // Default to 'RJ' for Rawat Jalan
            'tanggal_rawatan' => $this->sep_date,
            'patient_name' => $this->patient_name,
        ]);
    }
    protected function storeClaimDocuments(BpjsClaim $claim,$finalPath)
    {
        Storage::disk('public')->makeDirectory('raw-documents');

        foreach ($this->scanned_docs as $index => $file) {
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->storeAs('raw-documents', $filename, 'public');

            ClaimDocument::create([
                'bpjs_claims_id' => $claim->id,
                'filename' => $filename,
                'order' => $index,
                'disk' => Storage::disk('shared')->path($finalPath),
            ]);
        }
    }
    
    /* ====================
       ROTATE METHODS
       ==================== */
    public function rotateFile($index)
    {
        // Always rotate by exactly 90 degrees
        $rotationDegrees = 90;
        
        // Track visual state (0, 90, 180, 270)
        $key = $index === 0 ? 'sepFile' : $index;
        $this->rotations[$key] = (($this->rotations[$key] ?? 0) + 90) % 360;
        
        Log::info("Applying 90° rotation to file index: {$index}", [
            'new_rotation' => $this->rotations[$key]
        ]);

        // Apply rotation to physical file if needed
        if (isset($this->rotatedPaths[$index])) {
            $fullPath = storage_path('app/public/' . $this->rotatedPaths[$index]);
            
            if (file_exists($fullPath)) {
                $this->rotatePdf($fullPath, $rotationDegrees);
            }
        }
    }
    public function rotatePdf($filePath, $rotation ){
        // Log rotation attempt
        Log::debug('PDF Rotation - Starting rotation', [
            'file_path' => $filePath,
            'rotation' => $rotation,
            'file_exists' => file_exists($filePath),
            'file_size' => filesize($filePath),
        ]);

        // Validate parameters
        if (!in_array($rotation, [0, 90, 180, 270])) {
            Log::warning('PDF Rotation - Invalid rotation value', [
                'requested_rotation' => $rotation,
                'allowed_values' => [0, 90, 180, 270],
            ]);
            return false;
        }

        if (!file_exists($filePath)) {
            Log::warning('PDF Rotation - File does not exist', [
                'file_path' => $filePath,
            ]);
            return false;
        }

        try {
            Log::debug('PDF Rotation - Attempting to rotate with PdfRotate', [
                'rotation_degrees' => $rotation,
            ]);

            $rotator = new PdfRotate();
            // Add pre-rotation debug
            Log::debug('Before rotation', [
                'rotator_class' => get_class($rotator),
                'methods' => get_class_methods($rotator),
            ]);

            // Rotate the PDF
            $rotator->rotatePdf($filePath, $filePath, (int)$rotation);
           
            Log::info('PDF Rotation - Success');
            return true;
            
        } catch (\Exception $e) {
             Log::error('PDF Rotation - Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }
  
    public function render()
    {
        Log::info('render: Rendering component', [
            'rotatedPaths' => $this->rotatedPaths,
            'scanned_docs' => $this->scanned_docs,
            'sepFile' => $this->sepFile,
            'resumeFile' => $this->resumeFile,
            'billingFile' => $this->billingFile,
            'previewUrls' => $this->previewUrls,
        ]);
        return view('livewire.bpjs-rawat-jalan-form');
    }

     /* ====================
       ABANDONED METHODS
       ==================== */

    // public function searchPatient(){
    //         Log::info('searchPatient: Processing...');
    //         $this->validateOnly('simrs_rm_number');
    //         $patient = Patient::where('no_rkm_medis', $this->simrs_rm_number)->first();
    //         if ($patient) {
    //             //Validasi nama pasien
    //             $this->simrs_patient_name = trim($patient->nm_pasien);
    //             $this->simrs_bpjs_serial_number = trim($patient->no_peserta);
    //             Log::info('searchPatient: Validasi nama pasien...');
    //             Log::info('searchPatient: Nama pasien:', ['simrs_patient_name' => $this->simrs_patient_name, 'patient_name' => $this->patient_name]);
    //             $this->validatePatientData();
    //             Log::info('searchPatient: Patient validated:', ['patientValidated' => $this->patientValidated]);
    //         }else{
    //             $this->simrs_patient_name = '-';
    //             $this->simrs_bpjs_serial_number = '-';
    //             LivewireAlert::title('Pasien tidak ditemukan!')
    //             ->error()
    //             ->text('Pasient tidak ditemukan di SIMRS')
    //             ->toast()
    //             ->position('top-end')
    //             ->timer(6000) // Dismisses after 3 seconds
    //             ->show();
    //         }
    // }
}
