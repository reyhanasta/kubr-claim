<?php

namespace App\Livewire;

use App\Models\Patient;
use Livewire\Component;
use App\Models\BpjsClaim;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Str;
use App\Models\ClaimDocument;
use Livewire\WithFileUploads;
use App\Services\PdfMergerService;
use CzProject\PdfRotate\PdfRotate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BpjsClaimForm extends Component
{
    use WithFileUploads;
    public $no_rm = '';
    public $rmIcon = 'magnifying-glass';
    public $patient_name = '';
    public $rotations = []; // maps index => degrees (e.g., 90, 180, etc.)
    public $no_sep = '';
    public $no_kartu_bpjs = '';
    public $jenis_rawatan = 'RAWAT JALAN'; // Default to 'RAWAT JALAN'
    public $tanggal_rawatan ;
    public $scanned_docs = [];
    public $previewUrls = [];
    public $fileOrder = [];
    public $rotatedPaths = [];
    public $showPreviewModal = false;
    public $currentPreviewIndex = null;
    public $new_docs = []; // For new file uploads

    
     protected $rules = [
        'no_rm' => 'required|exists:pasien,no_rkm_medis',
        'tanggal_rawatan' => 'required|date',
        'jenis_rawatan' => 'required',
        'no_sep' => 'required',
        'scanned_docs.*' => 'required|file|mimes:pdf,jpg,png|max:2048', // 2MB max
    ];

    protected $messages = [
            'no_rm.required' => 'Nomor RM wajib diisi.',
            'no_rm.exists' => 'Nomor RM tidak ditemukan.',
            'tanggal_rawatan.required' => 'Tanggal rawatan wajib diisi.',
            'tanggal_rawatan.date' => 'Tanggal rawatan harus berupa tanggal.',
            'jenis_rawatan.required' => 'Jenis rawatan wajib diisi.',
            'no_sep.required' => 'Nomor SEP wajib diisi.',
            'scanned_docs.*.required' => 'File wajib diisi.',
            'scanned_docs.*.file' => 'File harus berformat PDF, JPG, atau PNG.',
            'scanned_docs.*.mimes' => 'File harus berformat PDF, JPG, atau PNG.',
            'scanned_docs.*.max' => 'File tidak boleh lebih dari 2MB.',
        ];

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


    /* ====================
       FILE ORDERING METHODS
       ==================== */

    public function updatedScannedDocs(){
        $this->validateOnly('scanned_docs.*');
        Log::info('updatedScannedDocs: Mulai memproses...');

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
        $this->rotations = [];
        $this->rotatedPaths = [];
        $this->previewUrls = [];
       
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
        
    }

    public function updatedNewDocs()
    {
        foreach ($this->new_docs as $doc) {
            $this->scanned_docs[] = $doc;
        }

        $this->new_docs = [];

        // PERBAIKAN 4: Gunakan updatedScannedDocs untuk konsistensi
        $this->updatedScannedDocs();
    }

    /* ====================
       ROTATE METHODS
       ==================== */
    public function rotateFile($index){
         // Always rotate by exactly 90 degrees
        $rotationDegrees = 90;
        //  $rotationDegrees = $this->rotations[$index];
        // Track visual state (0, 90, 180, 270)
        $this->rotations[$index] = (($this->rotations[$index] ?? 0) + 90) % 360;
        
        // Log the rotation action
        Log::info("Applying 90° rotation to file index: {$index}");

        // Apply rotation directly to file if already saved
        if (isset($this->rotatedPaths[$index])) {
            $fullPath = storage_path('app/public/' . $this->rotatedPaths[$index]);
            
            Log::debug('Rotation - Attempting to rotate physical file', [
                'index' => $index,
                'path' => $this->rotatedPaths[$index],
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
            ]);

            if (file_exists($fullPath)) {
                $rotationResult = $this->rotatePdf($fullPath, $rotationDegrees);
                
                Log::debug('Rotation - Physical file rotation attempted', [
                    'result' => $rotationResult ? 'success' : 'failed',
                    'applied_rotation' => $this->rotations[$index],
                ]);
            }
        }
        return false;

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

    /* ====================
       CLEAR PDF METHODS
       ==================== */
        /**
     * Clear all files
     */
    public function clearAllFiles(){
        LivewireAlert::title('Apakah yakin ingin menghapus semua file?')
        ->asConfirm()
        ->onConfirm('clearAllFilesProgress')
        ->show();
    }

    public function clearAllFilesProgress(){
       // PERBAIKAN 7: Membersihkan file fisik sebelum reset array
       foreach ($this->rotatedPaths as $path) {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
      $this->resetFileProperties();
    }

  
    protected function resetFileProperties()
    {
        $this->scanned_docs = [];
        $this->previewUrls = [];
        $this->fileOrder = [];
        $this->rotatedPaths = [];
        $this->rotations = [];
        $this->showPreviewModal = false;
        $this->currentPreviewIndex = null;
    }
    
    public function removeFile($index){
        try {
            // Check if the file exists in scanned_docs
            if (!isset($this->scanned_docs[$index])) {
                Log::error('File not found in scanned_docs', ['index' => $index]);
                return;
            }

            $fileToRemove = $this->scanned_docs[$index];
            
            // PERBAIKAN 10: Perbaikan pengecekan tipe objek
            $isUploadedFile = is_object($fileToRemove) && method_exists($fileToRemove, 'getRealPath');
            
            // Get the correct file name
            $filename = $isUploadedFile ? basename($fileToRemove->getRealPath()) : basename($fileToRemove);
            
            // PERBAIKAN 11: Hapus file rotated jika ada
            if (isset($this->rotatedPaths[$index]) && Storage::disk('public')->exists($this->rotatedPaths[$index])) {
                Storage::disk('public')->delete($this->rotatedPaths[$index]);
            }
            
            // Remove the file from arrays
            unset($this->scanned_docs[$index]);
            unset($this->previewUrls[$index]);
            unset($this->rotatedPaths[$index]);
            unset($this->rotations[$index]);
            unset($this->fileOrder[$index]);

            // Reindex to prevent ordering issues
            $this->scanned_docs = array_values($this->scanned_docs);
            $this->previewUrls = array_values($this->previewUrls);
            $this->rotatedPaths = array_values($this->rotatedPaths);
            $this->rotations = array_values($this->rotations);
            $this->fileOrder = array_keys($this->scanned_docs);

            // Clean up Livewire temp files
            if ($isUploadedFile && file_exists($fileToRemove->getRealPath())) {
                unlink($fileToRemove->getRealPath());
                Log::info('Successfully deleted Livewire temporary file', ['filename' => $filename]);
            }

            // Also attempt to remove from public storage if applicable
            $sharedPath = 'shared/' . $filename;
            if (Storage::disk('shared')->exists($sharedPath)) {
                Storage::disk('shared')->delete($sharedPath);
                Log::info('Successfully deleted from shared storage', ['path' => $sharedPath]);
            }

            // Clean up Livewire temp directory (if not yet moved to shared)
            $livewireTempPath = storage_path('app/livewire-tmp/' . $filename);
            if (file_exists($livewireTempPath)) {
                unlink($livewireTempPath);
                Log::info('Successfully deleted from Livewire temp', ['path' => $livewireTempPath]);
            }

            Log::info('File removal completed successfully');
        } catch (\Exception $e) {
            Log::error('Error in removeFile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'index' => $index,
            ]);
            session()->flash('error', 'Failed to remove the file. Please try again.');
        }
    }

    /* ====================
       FILE ORDERING METHODS
       ==================== */
     /**
     * Swap positions of two files
     */
    public function moveUp($index)
    {
        if ($index > 0) {
           // PERBAIKAN 8: Perbarui semua array terkait
            foreach (['scanned_docs', 'previewUrls', 'rotatedPaths', 'rotations'] as $arrayName) {
                if (isset($this->{$arrayName}[$index]) && isset($this->{$arrayName}[$index - 1])) {
                    [$this->{$arrayName}[$index - 1], $this->{$arrayName}[$index]] = [$this->{$arrayName}[$index], $this->{$arrayName}[$index - 1]];
                    Log::debug('File objects after swap:', $this->scanned_docs); // Untuk melihat urutan objek file
                    Log::debug('Rotated paths after swap:', $this->rotatedPaths);
                    Log::debug('Rotations array after swap:', $this->rotations);
                    Log::debug('Preview URLs after swap:', $this->previewUrls);
                    $this->rotations[$index - 1] = 0; // Reset rotation for the swapped file
                } else {
                Log::warning("--- moveUp: Inner 'if' (isset) for '$arrayName' FAILED. Index $index or " . ($index - 1) . " (or $index+1) might not be set. Skipping swap for '$arrayName'. ---"); // 5. Log jika isset gagal
            }
            }
        }
    }

  public function moveDown($index) {

    // Sesuaikan kondisi ini jika Anda menguji moveUp
    if ($index < count($this->scanned_docs) - 1) { 

        foreach (['scanned_docs', 'previewUrls', 'rotatedPaths', 'rotations'] as $arrayName) {
            // Sesuaikan kondisi ini untuk $index-1 jika di moveUp
            if (isset($this->{$arrayName}[$index]) && isset($this->{$arrayName}[$index + 1])) {
                // Sesuaikan ini untuk $index-1 jika di moveUp
                [$this->{$arrayName}[$index + 1], $this->{$arrayName}[$index]] = [$this->{$arrayName}[$index], $this->{$arrayName}[$index + 1]];
                Log::debug('File objects after swap:', $this->scanned_docs); // Untuk melihat urutan objek file
                Log::debug('Rotated paths after swap:', $this->rotatedPaths);
                Log::debug('Rotations array after swap:', $this->rotations);
                Log::debug('Preview URLs after swap:', $this->previewUrls);
                $this->rotations[$index + 1] = 0; // Reset rotation for the swapped file
            } else {
                Log::warning("--- moveDown: Inner 'if' (isset) for '$arrayName' FAILED. Index $index or " . ($index + 1) . " (or $index-1) might not be set. Skipping swap for '$arrayName'. ---"); // 5. Log jika isset gagal
            }
        }
    } else {
        Log::warning("--- moveDown: Outer 'if' condition NOT MET. Index: $index, File count: " . count($this->scanned_docs) . " ---"); // 6. Log jika if terluar gagal
    }
}

     /* ====================
       PATIENT METHODS
       ==================== */

    public function searchPatient()
    {
        // PERBAIKAN 12: Aktifkan kembali validasi
        // $this->validateOnly('no_rm');
        
        $patient = Patient::where('no_rkm_medis', $this->no_rm)->first();
        if ($patient) {
            $this->patient_name = $patient->nm_pasien;
            $this->no_kartu_bpjs = $patient->no_peserta;
            $this->rmIcon = 'check-circle';
        } else {
            $this->patient_name = '';
            $this->no_kartu_bpjs = '';
            $this->rmIcon = 'x-circle';
            LivewireAlert::title('Pasien tidak ditemukan!')
            ->error()
            ->text('Nomor Rekam Medis Belum terdaftar di SIMRS')
            ->toast()
            ->position('top-end')
            ->timer(4000) // Dismisses after 3 seconds
            ->show();
        }
    }

    /* ====================
       FORM SUBMISSION
       ==================== */
   
    public function submit(PdfMergerService $pdfMergeService)
    {
        $this->validate();

        $folderPath = $this->generateFolderPath();

        try {
            $outputPath = $this->generateOutputPath($folderPath);
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
            $this->cleanUpTempFiles();
            // Step 7: Reset form and notify success
            $this->reset();

            // LivewireAlert::title('Klaim berhasil dibuat!')
            //     ->success()
            //     ->text('Folder Klaim berhasil ditambahkan!')
            //     ->timer(4000)
            //     ->show();
            // LivewireAlert::title('Klaim berhasil dibuat!')
            //         ->text('Apakah ingin menambahkan klaim lain?')
            //         ->asConfirm()
            //         ->onConfirm('redirectToClaim')
            //         ->onDeny('redirectToClaim')
            //         ->show();

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

    protected function generateFolderPath()
    {
         // Set the locale to Indonesian
        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($this->tanggal_rawatan); // Use user-provided date
        $month = strtoupper($date->translatedFormat('F')); // e.g., "April 2025"
        $year = $date->format('Y'); // e.g., "April 2025"
        $day = $date->format('d'); // "4"

        // Use the user-provided date for the folder structure
        $jenisRawatan = $this->jenis_rawatan === 'RAWAT JALAN' ? 'RJ' : 'RI';

        return "{$month} REGULER {$year}/{$jenisRawatan}/{$day}/{$this->no_sep}";
    }

    protected function generateOutputPath(string $folderPath): string
    {
        $patientName = trim(explode(',', $this->patient_name)[0]);
        return "bpjs-claims/{$folderPath}/" . Str::upper($patientName) . '.pdf';
    }

    protected function createClaimRecord(): BpjsClaim
    {
        return BpjsClaim::create([
            'no_rkm_medis' => $this->no_rm,
            'no_kartu_bpjs' => $this->no_kartu_bpjs,
            'no_sep' => $this->no_sep,
            'jenis_rawatan' => $this->jenis_rawatan,
            'tanggal_rawatan' => $this->tanggal_rawatan,
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

    protected function cleanUpTempFiles()
    {
        foreach ($this->rotatedPaths as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function render()
    {
        return view('livewire.bpjs-claim-form');
    }
}
