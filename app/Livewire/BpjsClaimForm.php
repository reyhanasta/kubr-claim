<?php

namespace App\Livewire;

use App\Models\Patient;
use CzProject\PdfRotate\PdfRotate;
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

    public $currentPreviewUrl = '';
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

    public function rotatePdf($filePath, int $degrees){
        if (!in_array($degrees, [0, 90, 180, 270])) return;

        $rotator = new PdfRotate();
        $rotator->rotatePdf($filePath,$filePath, $degrees);
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
        unset($this->scanned_docs[$this->fileOrder[$index]]);
        unset($this->previewUrls[$index]);
        unset($this->fileOrder[$index]);
        $this->scanned_docs = array_values($this->scanned_docs);
        $this->previewUrls = array_values($this->previewUrls);
        $this->fileOrder = array_values($this->fileOrder);
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
            ->text('Pasien dengan no rm tidak terdaftar di SIMRS.')
            ->error()
            ->toast()
            ->position('top-end')
            ->show();
        }
    }

    public function updatedScannedDocs()
    {
        $this->validateOnly('scanned_docs.*');
        $this->previewUrls = [];
        $this->fileOrder = [];
        foreach ($this->scanned_docs as $index => $doc) {
            $filename = uniqid() . '_' . $doc->getClientOriginalName();

            // Simpan file ke disk 'public' -> /storage/temp/
            $storedPath = $doc->storeAs('temp', $filename, 'public');
            $fullPath = Storage::disk('public')->path($storedPath);

            // Apply rotation
            $rotation = $this->rotations[$index] ?? 0;
            if ($rotation !== 0) {
                $this->rotatePdf($fullPath, $rotation);
            }

              // Simpan untuk preview dan merge
            $this->rotatedPaths[] = $storedPath;
            $this->previewUrls[$index] = Storage::url($storedPath);
        }
    }


    // Generate folder structure and merge PDFs
    public function submit(PdfMergerService $pdfMergeService)
    {
        $this->validate();
        
        $folderPath = $this->generateFolderPath();
        
        try {
            //Final Output path
            $patientName = Str::slug($this->patient_name);
            $upperCasePatientName = Str::upper($patientName);
            $outputPath = "bpjs-claims/{$folderPath}/" . $upperCasePatientName . '.pdf';
            $finalPath = $pdfMergeService->mergePdfs($this->rotatedPaths, $outputPath);

                // $tempPaths = [];
                // foreach ($this->scanned_docs as $doc) {
                //     $tempPaths[] = $doc->store('temp', 'local'); // Simpan sementara di local disk
                // }

            
                
                $claim = BpjsClaim::create([
                    'no_rkm_medis' => $this->no_rm, // Track RM number
                    'no_kartu_bpjs' => $this->no_kartu_bpjs,
                    'no_sep' => $this->no_sep,
                    'jenis_rawatan' => $this->jenis_rawatan,
                    'tanggal_rawatan' => $this->tanggal_rawatan,
                    'patient_name' => $this->patient_name,
                    'file_path' => $outputPath,
                ]);

                // Cleanup
                foreach ($this->scanned_docs as $index => $file) {
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $file->storeAs('claims', $filename, 'public');
                    ClaimDocument::create([
                        'claim_id' => $claim->id,
                        'file_path' => $filename,
                        'order' => $index,
                    ]);
                }
                // Hapus isi form
                $this->reset();
                // Use Laravel SweetAlert2
                LivewireAlert::title('Klaim berhasil dibuat!')
                ->text('Klaim berhasil disimpan di dalam folder klaim.')
                ->success()
                ->position('center')
                ->timer(3400) // Dismisses after 2.4 seconds
                ->show();
            
            } catch (\Exception $e) {
                Log::error("BPJS Claim Error: " . $e->getMessage());
                LivewireAlert::title('Klaim gagal dibuat!')
                ->text('Terjadi kesalahan saat membuat klaim.')
                ->error()
                ->position('center')
                ->timer(3400)
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
