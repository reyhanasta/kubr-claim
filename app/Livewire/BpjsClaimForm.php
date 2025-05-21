<?php

namespace App\Livewire;

use App\Models\Patient;
use Livewire\Component;
use App\Models\BpjsClaim;
use Illuminate\Support\Str;
use App\Models\ClaimDocument;
use Livewire\WithFileUploads;
use App\Services\PdfMergerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use CzProject\PdfRotate\PdfRotate;

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
    public $currentPreviewUrl = '';
    public $new_docs = [];
    
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
    
        // PERBAIKAN 1: Menggunakan getter untuk currentPreviewUrl
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

    public function updatedScannedDocs()
    {
        $this->validateOnly('scanned_docs.*');

        $this->previewUrls = [];
        $this->rotatedPaths = [];
        

        foreach ($this->scanned_docs as $index => $doc) {
            $filename = uniqid() . '_' . $doc->getClientOriginalName();

             // Simpan file ke disk 'public' -> /storage/temp/
            $storedPath = $doc->storeAs('temp', $filename, 'public');
            $fullPath = storage_path('app/public/' . $storedPath);
    
            // Rotate PDF if needed
            $rotation = $this->rotations[$index] ?? 0;
            if ($rotation !== 0) {
                $this->rotatePdf($fullPath, $rotation);
            }
    
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
    public function rotateFile($index)
    {
        // Rotate clockwise by 90°, loop back to 0 after 270
        $this->rotations[$index] = ($this->rotations[$index] ?? 0) + 90;
        if ($this->rotations[$index] >= 360) {
            $this->rotations[$index] = 0;
        }

        // PERBAIKAN 5: Terapkan rotasi secara langsung ke file jika sudah disimpan
        if (isset($this->rotatedPaths[$index])) {
            $fullPath = storage_path('app/public/' . $this->rotatedPaths[$index]);
            if (file_exists($fullPath)) {
                $this->rotatePdf($fullPath, $this->rotations[$index]);
            }
        }
    }
    public function rotatePdf($filePath, $rotation)
    {
         // PERBAIKAN 6: Validasi parameter dan pastikan file ada
         if (!in_array($rotation, [0, 90, 180, 270]) || !file_exists($filePath)) {
            return false;
        }
        try {
            $rotator = new PdfRotate();
            $rotator->rotatePdf($filePath, $filePath, $rotation);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to rotate PDF', [
                'path' => $filePath,
                'rotation' => $rotation,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function clearAllFiles()
    {
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
    
        $this->scanned_docs = [];
        $this->previewUrls = [];
        $this->fileOrder = [];
        $this->rotatedPaths = [];
        $this->rotations = [];

        // Optional: reset preview state too
        $this->showPreviewModal = false;
        $this->currentPreviewIndex = null;
    }


    public function moveUp($index)
    {
        if ($index > 0) {
           // PERBAIKAN 8: Perbarui semua array terkait
           foreach (['scanned_docs', 'previewUrls', 'rotatedPaths', 'rotations'] as $arrayName) {
            if (isset($this->{$arrayName}[$index]) && isset($this->{$arrayName}[$index - 1])) {
                [$this->{$arrayName}[$index - 1], $this->{$arrayName}[$index]] =
                    [$this->{$arrayName}[$index], $this->{$arrayName}[$index - 1]];
            }
        }
        }
    }

    public function moveDown($index)
    {
        if ($index < count($this->scanned_docs) - 1) {
            // PERBAIKAN 9: Perbarui semua array terkait (sama seperti moveUp)
            foreach (['scanned_docs', 'previewUrls', 'rotatedPaths', 'rotations'] as $arrayName) {
                if (isset($this->{$arrayName}[$index]) && isset($this->{$arrayName}[$index + 1])) {
                    [$this->{$arrayName}[$index + 1], $this->{$arrayName}[$index]] =
                        [$this->{$arrayName}[$index], $this->{$arrayName}[$index + 1]];
                }
            }
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
   
    public function submit(PdfMergerService $pdfMergeService)
    {
        $this->validate();

        $folderPath = $this->generateFolderPath();

        try {
            // PERBAIKAN 13: Perbaikan logika merge files dan pastikan direktori tujuan ada
            $outputDir = "bpjs-claims/{$folderPath}";
            // Storage::disk('public')->makeDirectory($outputDir);

            // Prepare final PDF output path
            $patientName = trim(explode(',', $this->patient_name)[0]);
            $upperCasePatientName = Str::upper($patientName);
            $outputPath = "{$outputDir}/" . $upperCasePatientName . '.pdf';
            
            // Use rotatedPaths yang sudah diproses sebelumnya
            if (empty($this->rotatedPaths)) {
                throw new \Exception("No files available to merge");
            }
            
            // Step 3: Merge all PDFs
            $finalPath = $pdfMergeService->mergePdfs($this->rotatedPaths, $outputPath);

            // Step 4: Save claim data
            $claim = BpjsClaim::create([
                'no_rkm_medis' => $this->no_rm,
                'no_kartu_bpjs' => $this->no_kartu_bpjs,
                'no_sep' => $this->no_sep,
                'jenis_rawatan' => $this->jenis_rawatan,
                'tanggal_rawatan' => $this->tanggal_rawatan,
                'patient_name' => $this->patient_name,
                'file_path' => $finalPath, // Added back
            ]);

            // Step 5: Save each uploaded file with order
            foreach ($this->scanned_docs as $index => $file) {
                // PERBAIKAN 14: Pastikan direktori claims ada
                if (!Storage::disk('public')->exists('claims')) {
                    Storage::disk('public')->makeDirectory('claims');
                }
                
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->storeAs('claims', $filename, 'public');

                ClaimDocument::create([
                    'bpjs_claims_id' => $claim->id,
                    'filename' => $filename,
                    'order' => $index,
                ]);
            }

            // Step 6: Clean up temp files
            // PERBAIKAN 15: Aktifkan pembersihan file temporary
            foreach ($this->rotatedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

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


    public function render()
    {
        return view('livewire.bpjs-claim-form');
    }
}
