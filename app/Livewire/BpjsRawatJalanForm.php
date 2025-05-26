<?php

namespace App\Livewire;

use CzProject\PdfRotate\PdfRotate;
use Livewire\Component;
use App\Models\BpjsClaim;
use Illuminate\Support\Str;
use App\Models\ClaimDocument;
use Livewire\WithFileUploads;
use App\Services\PdfReadService;
use App\Services\PdfMergerService;
use Illuminate\Support\Facades\Log;
use App\Services\GenerateFolderService;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BpjsRawatJalanForm extends Component
{
    use WithFileUploads;
    public $scanned_docs = []; // For scanned documents
    public $new_docs = []; // For new file uploads
    public $awal_medis=[];
    public $scanned_docs_count = 0;
    public $patient_name; 
    public $sep_date;
    public $sep_number;
    public $bpjs_serial_number;
    public $medical_record_number;
    
    public $pdfText;
    public $rmIcon = 'magnifying-glass';
    public $rotations = []; // maps index => degrees (e.g., 90, 180, etc.)  
    public $no_kartu_bpjs = '';
    public $jenis_rawatan = 'RAWAT JALAN'; // Default to 'RAWAT JALAN'
    public $previewUrls = [];
    public $fileOrder = [];
    public $rotatedPaths = [];
    public $showPreviewModal = false;
    public $currentPreviewIndex = null;


    protected $rules = [
        'scanned_docs.*' => 'required|file|mimes:pdf|max:2048', // 2MB max
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
        Log::info('updatedScannedDocs: Proses selesai. Rotated paths:', ['rotatedPaths' => $this->rotatedPaths]);
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


    public function pdfProcessing (PdfReadService $pdfReadService)
    {
       
            $this->validateOnly('scanned_docs.*');
            Log::info('Processing scanned documents...');
            
                $this->pdfText = $pdfReadService->getPdfTextwithSpatie($this->scanned_docs[0] ?? null);
                $data = $pdfReadService->extractPdf($this->pdfText);
                $this->fill($data);
          
            Log::info(
                "SEP: File processed successfully.",
                ['scanned_docs' => $this->scanned_docs[0],'sep_number' => $this->sep_number, 'bpjs_serial_number' => $this->bpjs_serial_number]);
        

    }

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
            $pdfMergeService->cleanUpTempFiles($this->rotatedPaths);
            // Step 7: Reset form and notify success
            $this->reset();

            LivewireAlert::title('Klaim berhasil dibuat!')
                ->success()
                ->text('Folder Klaim berhasil ditambahkan!')
                ->timer(2400)
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
  
    public function render()
    {
        if($this->scanned_docs){
            $this->pdfProcessing(new PdfReadService());
        }
        return view('livewire.bpjs-rawat-jalan-form');
    }
}
