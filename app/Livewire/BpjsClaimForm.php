<?php

namespace App\Livewire;

use App\Models\Patient;
use Livewire\Component;
use App\Models\BpjsClaim;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use App\Services\PdfMergerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BpjsClaimForm extends Component
{
    use WithFileUploads;
    public $no_rm = '';

    public $rmIcon = 'magnifying-glass';
    public $patient_name = '';
    public $no_sep = '';
    public $no_kartu_bpjs = '';
    public $jenis_rawatan = 'RAWAT JALAN'; // Default to 'RAWAT JALAN'
    public $tanggal_rawatan ;
    public $scanned_docs = [];
    public $previewUrls = [];
    public $fileOrder = [];
    public $showPreviewModal = false;
    public $currentPreviewIndex = null;
    
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
    

    public function previewFile($index)
    {
        if (isset($this->scanned_docs[$index])) {
            $this->currentPreviewIndex = $index;
            $this->showPreviewModal = true;
        }
    }

    public function getCurrentPreviewUrlProperty()
    {
        if ($this->currentPreviewIndex === null) {
            return null;
        }
        
        $filename = $this->scanned_docs[$this->currentPreviewIndex]->getFilename();
        return route('preview-temp-file', ['filename' => $filename]);
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
        $this->fileOrder = [];
        foreach ($this->scanned_docs as $index => $doc) {
            $this->previewUrls[$index] = $doc->temporaryUrl();
            $this->fileOrder[$index] = $index;
        }
    }

    public function moveUp($index)
    {
        if ($index > 0) {
            // Swap the scanned_docs order
            $tempDoc = $this->scanned_docs[$index];
            $this->scanned_docs[$index] = $this->scanned_docs[$index - 1];
            $this->scanned_docs[$index - 1] = $tempDoc;

            // Swap the fileOrder to match the docs order
            $tempOrder = $this->fileOrder[$index];
            $this->fileOrder[$index] = $this->fileOrder[$index - 1];
            $this->fileOrder[$index - 1] = $tempOrder;
        }
    }

    public function moveDown($index)
    {
         if ($index < count($this->fileOrder) - 1) {
            // Swap the scanned_docs order
            $tempDoc = $this->scanned_docs[$index];
            $this->scanned_docs[$index] = $this->scanned_docs[$index + 1];
            $this->scanned_docs[$index + 1] = $tempDoc;

            // Swap the fileOrder to match the docs order
            $tempOrder = $this->fileOrder[$index];
            $this->fileOrder[$index] = $this->fileOrder[$index + 1];
            $this->fileOrder[$index + 1] = $tempOrder;
        }
    }
    public function removeFile($index)
    {
        Log::info('removeFile called', ['index' => $index, 'fileOrder' => $this->fileOrder, 'scanned_docs_count' => count($this->scanned_docs)]);
        
        // Check if the index is valid
        if (!isset($this->fileOrder[$index])) {
            Log::error('Invalid index in fileOrder', ['index' => $index, 'fileOrder' => $this->fileOrder]);
            return;
        }

        $fileKey = $this->fileOrder[$index];
        if (!isset($this->scanned_docs[$fileKey])) {
            Log::error('File not found in scanned_docs', ['index' => $index, 'fileKey' => $fileKey, 'scanned_docs' => array_keys($this->scanned_docs)]);
            return;
        }

        try {
            // Get the file to remove
            $fileToRemove = $this->scanned_docs[$fileKey];
            $isUploadedFile = is_object($fileToRemove) && method_exists($fileToRemove, 'getFilename');
            $filename = $isUploadedFile ? $fileToRemove->getFilename() : basename($fileToRemove);
            
            Log::info('Removing file', [
                'fileKey' => $fileKey,
                'isUploadedFile' => $isUploadedFile,
                'filename' => $filename,
                'fileToRemove' => is_string($fileToRemove) ? $fileToRemove : get_class($fileToRemove)
            ]);
            
            // Remove from arrays first to prevent race conditions
            unset($this->scanned_docs[$fileKey]);
            if (isset($this->previewUrls[$index])) {
                unset($this->previewUrls[$index]);
            }
            unset($this->fileOrder[$index]);
            
            // Reindex arrays
            $this->scanned_docs = array_values($this->scanned_docs);
            $this->previewUrls = array_values($this->previewUrls);
            $this->fileOrder = array_values($this->fileOrder);
            
            Log::info('Arrays after removal and reindexing', [
                'new_scanned_docs_count' => count($this->scanned_docs),
                'new_previewUrls_count' => count($this->previewUrls),
                'new_fileOrder' => $this->fileOrder
            ]);
            
            // Clean up files
            if ($isUploadedFile && $fileToRemove) {
                Log::info('Deleting Livewire temporary file', ['filename' => $filename]);
                $fileToRemove->delete();
                Log::info('Successfully deleted Livewire temporary file');
            } else {
                // For files that have been moved to storage
                $storagePath = str_replace('storage/', 'public/', $fileToRemove);
                Log::info('Attempting to delete from storage', ['storagePath' => $storagePath]);
                
                if (Storage::exists($storagePath)) {
                    Storage::delete($storagePath);
                    Log::info('Successfully deleted from storage', ['path' => $storagePath]);
                } else {
                    Log::warning('File not found in storage', ['path' => $storagePath]);
                }
                
                // Also check Livewire's temp directory
                $livewireTempPath = 'livewire-tmp/' . $filename;
                Log::info('Checking Livewire temp directory', ['path' => $livewireTempPath]);
                
                if (Storage::exists($livewireTempPath)) {
                    Storage::delete($livewireTempPath);
                    Log::info('Successfully deleted from Livewire temp', ['path' => $livewireTempPath]);
                } else {
                    Log::info('File not found in Livewire temp', ['path' => $livewireTempPath]);
                }
            }
            
            Log::info('File removal completed successfully');
        } catch (\Exception $e) {
            Log::error('Error in removeFile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'index' => $index,
                'fileOrder' => $this->fileOrder,
                'scanned_docs' => $this->scanned_docs
            ]);
            throw $e; // Re-throw to see the error in the UI during development
        }
    }

    public function searchPatient()
    {
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
            ->error()->show();
        }
    }

    // Generate folder structure and merge PDFs
    public function submit(PdfMergerService $pdfMergeService)
    {
        $this->validate();
        
        $folderPath = $this->generateFolderPath();
      try {
            $tempPaths = [];
            foreach ($this->scanned_docs as $doc) {
                $tempPaths[] = $doc->store('temp', 'local'); // Simpan sementara di local disk
            }
            $patientName = Str::slug($this->patient_name);
            $upperCasePatientName = Str::upper($patientName);
            $outputPath = "bpjs-claims/{$folderPath}/" . $upperCasePatientName . '.pdf';
        
            $finalPath = $pdfMergeService->mergePdfs($tempPaths, $outputPath);
            BpjsClaim::create([
                'no_rkm_medis' => $this->no_rm, // Track RM number
                'no_kartu_bpjs' => $this->no_kartu_bpjs,
                'no_sep' => $this->no_sep,
                'jenis_rawatan' => $this->jenis_rawatan,
                'tanggal_rawatan' => $this->tanggal_rawatan,
                'patient_name' => $this->patient_name,
                'file_path' => $finalPath,
            ]);

            // Cleanup
            foreach ($tempPaths as $path) {
                Storage::delete($path);
            }
             // Hapus isi form
            $this->reset();
            // Use Laravel SweetAlert2
            LivewireAlert::title('Klaim berhasil dibuat!')
            ->success()
            ->show();
        
        } catch (\Exception $e) {
            Log::error("BPJS Claim Error: " . $e->getMessage());
            LivewireAlert::title('Klaim gagal dibuat!')
            ->error()
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
