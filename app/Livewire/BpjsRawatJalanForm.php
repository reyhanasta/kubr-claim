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
use Illuminate\Support\Facades\Log;
use App\Services\GenerateFolderService;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BpjsRawatJalanForm extends Component
{
    use WithFileUploads;

    // Dokumen utama
    public $scanned_docs = ['sepFile'=>'','billingFile'=>'','resumeFile'=>''];
    public $rotatedPaths = [];
    public $previewUrls = [];

    // File upload individual
    public $sepFile;
    public $resumeFile;
    public $billingFile;
    public $fileLIP; // ✅ file tambahan
    
    // Flags
    public $uploading = false;
    public $showUploadedData = false;
    
    // Data pasien
    public $patient_name; 
    public $sep_date;
    public $sep_number;
    public $bpjs_serial_number;
    public $medical_record_number;
    public $jenis_rawatan = 'RJ'; // Jenis rawatan default RJ
    public $patient_class;
    public $confirmPatient = false;

    protected $rules = [
        'scanned_docs.*' => 'nullable|file|mimes:pdf|max:2048',
        'fileLIP'        => 'nullable|file|mimes:pdf|max:2048', // ✅ validasi LIP
    ];


    protected $messages = [
        'medical_record_number.required' => 'Nomor RM wajib diisi.',
        'medical_record_number.exists' => 'Nomor RM tidak ditemukan.',
        'sep_date.required' => 'Tanggal rawatan wajib diisi.',
        'sep_date.date' => 'Tanggal rawatan harus berupa tanggal.',
        'sep_number.required' => 'Nomor SEP wajib diisi.',
        'scanned_docs.*.required' => 'File wajib diisi.',
        'scanned_docs.*.file' => 'File harus berformat PDF.',
        'scanned_docs.*.mimes' => 'File harus berformat PDF.',
        'scanned_docs.*.max' => 'File tidak boleh lebih dari 2MB.',
    ];

    protected $listeners = ['cancelUploadTimeout' => 'handleUploadTimeout'];

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

    /* ====================
       PREVIEW
       ==================== */
    public function getCurrentPreviewUrlProperty()
    {
        return $this->currentPreviewIndex !== null && isset($this->previewUrls[$this->currentPreviewIndex])
            ? $this->previewUrls[$this->currentPreviewIndex]
            : '';
    }

    /* ====================
       FILE UPLOAD
       ==================== */
    public function updatedSepFile(PdfReadService $pdfReadService)
    {
        try {
            $this->uploading = true;
            $this->validateOnly('sepFile');

            // Extract text SEP
            $pdfText = $pdfReadService->getPdfTextwithSpatie($this->sepFile);
            $data = $pdfReadService->extractPdf($pdfText);

            if (!$data) {
                LivewireAlert::title('Format dokumen salah!')
                    ->error()
                    ->text('Silahkan upload ulang file SEP')
                    ->timer(10000)
                    ->show();
                return;
            }

            $this->fill($data);
            $this->showUploadedData = true;

            // Save for preview
            $filename = uniqid() . '_' . $this->sepFile->getClientOriginalName();
            $storedPath = $this->sepFile->storeAs('temp', $filename, 'public');

            $this->scanned_docs['sepFile'] = $this->sepFile;
            $this->rotatedPaths['sepFile'] = $storedPath;
            
            // Use url() helper instead of asset() - this uses current request URL
            $this->previewUrls['sepFile'] = url('storage/' . $storedPath);

            Log::info('SEP file processed', [
                'filename' => $filename,
                'path' => $storedPath,
                'url' => $this->previewUrls['sepFile']
            ]);
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
        if ($this->resumeFile) {
            $filename = uniqid() . '_' . $this->resumeFile->getClientOriginalName();
            $storedPath = $this->resumeFile->storeAs('temp', $filename, 'public');

            $this->scanned_docs['resumeFile'] = $this->resumeFile;
            $this->rotatedPaths['resumeFile'] = $storedPath;
            $this->previewUrls['resumeFile'] = Storage::url($storedPath);

            Log::info("Resume file uploaded", ['filename' => $filename]);
        }
    }

    public function updatedBillingFile()
    {
        if ($this->billingFile) {
            $filename = uniqid() . '_' . $this->billingFile->getClientOriginalName();
            $storedPath = $this->billingFile->storeAs('temp', $filename, 'public');

            $this->scanned_docs['billingFile'] = $this->billingFile;
            $this->rotatedPaths['billingFile'] = $storedPath;
            $this->previewUrls['billingFile'] = Storage::url($storedPath);

            Log::info("Billing file uploaded", ['filename' => $filename]);
        }
    }

    public function updatedFileLIP()
    {
        if ($this->fileLIP) {
            $filename = uniqid() . '_' . $this->fileLIP->getClientOriginalName();
            $storedPath = $this->fileLIP->storeAs('temp', $filename, 'public');

            $this->scanned_docs['fileLIP'] = $this->fileLIP;
            $this->previewUrls['fileLIP'] = Storage::url($storedPath);

            Log::info("LIP file uploaded", [
                'filename' => $filename,
                'path' => $storedPath,
            ]);
        }
    }

    public function cancelUpload()
    {
        $this->uploading = false;
        $this->reset(['sepFile', 'resumeFile', 'billingFile', 'scanned_docs', 'previewUrls', 'rotatedPaths', 'showUploadedData']);

        foreach ($this->rotatedPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
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

     public function cancelForm()
    {
        $this->reset(
            'scanned_docs',
            'rotatedPaths',
            'previewUrls',
            'sepFile',
            'resumeFile',
            'billingFile',
            'fileLIP',
            'showUploadedData'
        );
        return redirect(request()->header('Referer'));
        
    }

    /* ====================
       SUBMIT
       ==================== */
    public function submit(PdfMergerService $pdfMergeService, GenerateFolderService $generateFolderService)
    {
        $this->validate();

        try {
            $outputDir = $generateFolderService->generateOutputPath($this->sep_date, $this->sep_number,$this->jenis_rawatan);
            $pdfOutputPath = $outputDir . Str::upper($this->patient_name) . '.pdf';
            // Urutan fix: SEP -> Billing -> Resume
            $orderedFiles = [];
            if (!empty($this->rotatedPaths['sepFile'])) $orderedFiles[] = $this->rotatedPaths['sepFile'];
            if (!empty($this->rotatedPaths['billingFile'])) $orderedFiles[] = $this->rotatedPaths['billingFile'];
            if (!empty($this->rotatedPaths['resumeFile'])) $orderedFiles[] = $this->rotatedPaths['resumeFile'];
            
            if (empty($orderedFiles)) throw new \Exception("Tidak ada file yang bisa digabungkan");
            
            $finalPath = $pdfMergeService->mergePdfs($orderedFiles, $pdfOutputPath);
            
            $claim = $this->createClaimRecord();
            $this->storeClaimDocuments($claim, $finalPath);
            // Simpan LIP kalau ada
            if (!empty($this->fileLIP)) {
                $lipFilename = 'LIP.pdf';
                $lipPath = dirname($pdfOutputPath) . '/' . $lipFilename;
                
                 // Simpan file ke shared storage
                $this->fileLIP->storeAs(dirname($pdfOutputPath), $lipFilename, 'shared');
                ClaimDocument::create([
                    'bpjs_claims_id' => $claim->id,
                    'filename' => $lipFilename,
                    'order' => 'LIP',
                    'disk' => Storage::disk('shared')->path($lipPath),
                ]);

                Log::info("LIP file saved", ['lip_file' => $lipPath]);
            }

            $this->cleanUpAfterSubmit($pdfMergeService);


            LivewireAlert::title('Klaim berhasil dibuat!')
                ->text('Dokumen digabung sesuai urutan SEP → Billing → Resume')
                ->success()
                ->show();
        } catch (\Exception $e) {
            Log::error("BPJS Claim Error: " . $e->getMessage());
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
            'jenis_rawatan' => $this->jenis_rawatan,
            'tanggal_rawatan' => $this->sep_date,
            'patient_name' => $this->patient_name,
            'patient_class' => $this->patient_class,
        ]);
    }

    protected function storeClaimDocuments(BpjsClaim $claim, $finalPath)
    {
        Storage::disk('public')->makeDirectory('raw-documents');

        foreach ($this->scanned_docs as $index => $file) {
            if (!$file || $index === 'fileLIP') continue; // ✅ skip LIP dari merge list

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

    protected function cleanUpAfterSubmit($pdfMergeService)
    {
        $pdfMergeService->cleanupTempFiles($this->rotatedPaths);
        $this->reset();
    }

    public function render()
    {
        return view('livewire.bpjs-rawat-jalan-form');
    }
}
