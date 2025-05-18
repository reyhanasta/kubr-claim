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

class BpjsClaimForm extends Component
{
    use WithFileUploads;
    public $no_rm = '';
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
        'scanned_docs.*' => 'file|mimes:pdf,jpg,png|max:2048', // 2MB max
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
        if (is_null($this->currentPreviewIndex)) {
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
        $this->validateOnly('no_rm');
        $patient = Patient::where('no_rkm_medis', $this->no_rm)->first();
        if ($patient) {
            $this->patient_name = $patient->nm_pasien;
            $this->no_kartu_bpjs = $patient->no_peserta;
        } else {
            $this->reset(['nama', 'no_kartu_bpjs']);
            $this->addError('no_rm', 'Pasien tidak ditemukan.');
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
            $outputPath = "bpjs-claims/{$folderPath}/" . Str::slug($this->patient_name) . '.pdf';
        
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
             // Hapus file temp
            // Storage::disk('local')->delete($tempPaths);


            $this->resetExcept(['no_rm']); // Clear form after submission
            session()->flash('success', 'Klaim berhasil dibuat!');
        } catch (\Exception $e) {
            Log::error("BPJS Claim Error: " . $e->getMessage());
            session()->flash('error', 'Gagal membuat klaim: ' . $e->getMessage());
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

    // protected function generateFolderPath(): string
    // {
    //     $date = now()->parse($this->tanggal_rawatan); // Use user-provided date
        
    //     return sprintf(
    //         "%s/%s/%d/%s",
    //         strtoupper($date->format('F Y')), // APRIL 2025
    //         $this->jenis_rawatan === 'RAWAT JALAN' ? 'RJ' : 'RI',
    //         $date->day, // 4 (no leading zero)
    //         $this->no_sep
    //     );
    // }

    protected function mergePdfs($folderPath)
    {
       
       
        // Save uploaded files
        $paths = [];
        foreach ($this->scanned_docs as $doc) {
            $paths[] = $doc->store("temp");
        }
         
        // Merge logic (simplified)
        $outputPath = "bpjs-claims/{$folderPath}/" . Str::slug($this->patient_name) . '.pdf';
        Storage::disk('shared')->put($outputPath, 'Merged PDF content here');
        
        return $outputPath;
    }

    // protected function mergePdfs(string $folderPath): string
    // {
    //     $mergedContent = $this->generatePdfContent(); // Customize this
        
    //     $fileName = Str::slug($this->patient_name) . '.pdf';
    //     $fullPath = "bpjs-claims/{$folderPath}/{$fileName}";

    //     Storage::disk('shared')->put($fullPath, $mergedContent);
    //     // return response()->file(Storage::disk('shared')->path($fullPath))->getContent();
        
    //     return $fullPath;
    // }

    protected function generatePdfContent(): string
    {
        // Implement your PDF generation logic here
        return "Merged PDF content for {$this->patient_name}";
    }
    public function render()
    {
        return view('livewire.bpjs-claim-form');
    }
}
