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
        // sleep(2); // simulate preview generation delay

        $this->validateOnly('scanned_docs.*');
        $this->previewUrls = [];
        $this->fileOrder = [];
        foreach ($this->scanned_docs as $index => $doc) {
            // $this->previewUrls[$index] = $doc->temporaryUrl();
            $path = $doc->storeAs('temp', $doc->getFilename(), 'public');
            $this->previewUrls[] = Storage::url($path);
            $this->fileOrder[$index] = $index;
        }
    }

    public function updatedNewDocs()
    {
        foreach ($this->new_docs as $doc) {
            $this->scanned_docs[] = $doc;
        }

        $this->new_docs = [];

        // Re-generate preview/order
        $this->previewUrls = [];
        foreach ($this->scanned_docs as $index => $doc) {
            $path = $doc->storeAs('temp', $doc->getFilename(), 'public');
            $this->previewUrls[] = Storage::url($path);
        }

        $this->fileOrder = array_keys($this->scanned_docs);
    }

    public function clearAllFiles()
    {
        LivewireAlert::title('Apakah yakin ingin menghapus semua file?')
        ->asConfirm()
        ->onConfirm('clearAllFilesProgress')
        ->show();
    }

    public function clearAllFilesProgress(){
        $this->scanned_docs = [];
        $this->previewUrls = [];
        $this->fileOrder = [];

        // Optional: reset preview state too
        $this->showPreviewModal = false;
        $this->currentPreviewIndex = null;
    }


    public function moveUp($index)
    {
        if ($index > 0) {
            // Swap scanned_docs
            [$this->scanned_docs[$index - 1], $this->scanned_docs[$index]] =
                [$this->scanned_docs[$index], $this->scanned_docs[$index - 1]];

            // Swap previewUrls
            [$this->previewUrls[$index - 1], $this->previewUrls[$index]] =
                [$this->previewUrls[$index], $this->previewUrls[$index - 1]];

            // Swap fileOrder (optional)
            [$this->fileOrder[$index - 1], $this->fileOrder[$index]] =
                [$this->fileOrder[$index], $this->fileOrder[$index - 1]];
        }
    }

    public function moveDown($index)
    {
        if ($index < count($this->scanned_docs) - 1) {
            // Swap scanned_docs
            [$this->scanned_docs[$index + 1], $this->scanned_docs[$index]] =
                [$this->scanned_docs[$index], $this->scanned_docs[$index + 1]];

            // Swap previewUrls
            [$this->previewUrls[$index + 1], $this->previewUrls[$index]] =
                [$this->previewUrls[$index], $this->previewUrls[$index + 1]];

            // Swap fileOrder (optional)
            [$this->fileOrder[$index + 1], $this->fileOrder[$index]] =
                [$this->fileOrder[$index], $this->fileOrder[$index + 1]];
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
            $isUploadedFile = is_object($fileToRemove) && method_exists($fileToRemove, 'getRealPath');
            
            // Get the correct file name
            $filename = $isUploadedFile ? basename($fileToRemove->getRealPath()) : basename($fileToRemove);
            
            // Remove the file from arrays
            unset($this->scanned_docs[$index]);
            unset($this->previewUrls[$index]);
            unset($this->fileOrder[$index]);

            // Reindex to prevent ordering issues
            $this->scanned_docs = array_values($this->scanned_docs);
            $this->previewUrls = array_values($this->previewUrls);
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
                $patientName = trim(explode(',', $this->patient_name)[0]);
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
                ->text('Folder Klaim berhasil ditambahkan!')
                ->timer(2400) // Dismisses after 3 seconds
                ->show();
            
            } catch (\Exception $e) {
                Log::error("BPJS Claim Error: " . $e->getMessage());
                LivewireAlert::title('Klaim gagal dibuat!')
                ->error()
                ->text('Terjadi kegagalan saat penyimpanan file. Silahkan cek kembali kelengkapan data!')
                ->timer(2400) // Dismisses after 3 seconds
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
