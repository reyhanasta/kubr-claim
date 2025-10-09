<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\BpjsClaim;
use App\Jobs\BackupFileJob;
use Illuminate\Support\Str;
use App\Models\ClaimDocument;
use App\Services\PdfReadService;
use App\Services\PdfMergerService;
use App\Services\GenerateFolderService;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BpjsRawatJalanForm extends Component
{
    use WithFileUploads;

    // Dokumen utama
    public $scanned_docs = ['sepFile'=>'','sepRJFile'=>'','billingFile'=>'','resumeFile'=>''];
    public $rotatedPaths = [];
    public $previewUrls = [];

    // File upload individual
    public $sepFile;
    public $sepRJFile; // ✅ file tambahan
    public $resumeFile;
    public $billingFile;
    public $fileLIP; // ✅ file tambahan
    
    // Flags
    public $uploading = false;
    public $showUploadedData = false;
    
    // Data pasien
    public $patient_name; 
    public $sep_date;
    public $sep_date_label = 'Tanggal SEP';
    public $sep_number;
    public $bpjs_serial_number;
    public $medical_record_number;
    public $jenis_rawatan = 'RJ'; // Jenis rawatan default RJ
    public $patient_class;
    public $confirmPatient = false;

    protected $rules = [
        'sepFile'     => 'required|file|mimes:pdf|max:2048', // SEP wajib
        'billingFile' => 'required|file|mimes:pdf|max:2048', // Billing wajib
        'resumeFile'  => 'required|file|mimes:pdf|max:2048', // Resume wajib
        'fileLIP'     => 'nullable|file|mimes:pdf|max:2048', // LIP opsional
        'sep_date'    => 'required|date',
        'sep_number'  => 'required|string|max:50|unique:bpjs_claims,no_sep',
        'medical_record_number' => 'required|string|max:50',
    ];

    protected $messages = [
        'sepFile.required'     => 'File SEP wajib diunggah.',
        'sepFile.mimes'        => 'File SEP harus berformat PDF.',
        'billingFile.required' => 'File Billing wajib diunggah.',
        'billingFile.mimes'    => 'File Billing harus berformat PDF.',
        'resumeFile.required'  => 'File Resume Medis wajib diunggah.',
        'resumeFile.mimes'     => 'File Resume Medis harus berformat PDF.',
        'fileLIP.mimes'        => 'File LIP harus berformat PDF.',
        'sep_number.required'  => 'Nomor SEP wajib diisi.',
        'sep_date.required'    => 'Tanggal SEP wajib diisi.',
        'sep_date.date'        => 'Tanggal SEP harus berupa format tanggal yang valid.',
        'medical_record_number.required' => 'Nomor RM wajib diisi.',
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
             try {
                $this->uploading = true;
                $this->validateOnly('sepFile');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->showValidationErrors($e->validator);
                $this->uploading = false;
                return;
            }

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
            if($this->jenis_rawatan == 'RI'){
                $this->sep_date_label = 'Tanggal Pulang';
                $this->sep_date = null;
            }
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

    public function updatedSEPRJFile()
    {
        if ($this->sepRJFile) {
            $filename = uniqid() . '_' . $this->sepRJFile->getClientOriginalName();
            $storedPath = $this->sepRJFile->storeAs('temp', $filename, 'public');

            $this->scanned_docs['sepRJFile'] = $this->sepRJFile;
            $this->rotatedPaths['sepRJFile'] = $storedPath;
            $this->previewUrls['sepRJFile'] = Storage::url($storedPath);

            Log::info("SEP RJ file uploaded", ['filename' => $filename]);
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
        $this->reset(['sepFile', 'sepRJFile', 'resumeFile', 'billingFile', 'scanned_docs', 'previewUrls', 'rotatedPaths', 'showUploadedData']);

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
            'sepRJFile',
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
        try {
        $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->showValidationErrors($e->validator);
            return;
        }


        // 1️⃣ Validasi cepat tanpa lanjut ke proses file
        if (!$this->sepFile || !$this->billingFile || !$this->resumeFile) {
            return LivewireAlert::error('Semua file wajib diunggah sebelum menyimpan klaim.')->show();
        }

        try {
            // 2️⃣ Siapkan direktori output & nama file PDF akhir
            $outputDir = $generateFolderService->generateOutputPath($this->sep_date, $this->sep_number, $this->jenis_rawatan);
            // $patientNameSafe = Str::of($this->patient_name)->upper()->replaceMatches('/[^A-Z0-9_\-]/', '_');
            $patientNameSafe = Str::of($this->patient_name)->upper();
            $pdfOutputPath = $outputDir . "{$patientNameSafe}.pdf";

            // 3️⃣ Tentukan urutan file yang akan digabung (SEP -> Billing -> Resume)
            if($this->rotatedPaths)
            $orderedFiles = collect([
                $this->rotatedPaths['sepFile'] ?? null,
                $this->rotatedPaths['sepRJFile'] ?? null,
                $this->rotatedPaths['resumeFile'] ?? null,
                $this->rotatedPaths['billingFile'] ?? null,
            ])->filter()->values()->all();
                
            if (empty($orderedFiles)) {
                Log::error("No files to merge", $orderedFiles);
                throw new \Exception("Tidak ada file yang bisa digabungkan");
            }
            // 4️⃣ Gabungkan PDF secara efisien (streamed)
            $finalPath = $pdfMergeService->mergePdfs($orderedFiles, $pdfOutputPath);

            // 5️⃣ Simpan record klaim ke database
            $claim = $this->createClaimRecord();

            // 6️⃣ Simpan dokumen utama klaim (hasil merge)
            $this->storeClaimDocuments($claim, $finalPath);

            // 7️⃣ Jika ada file LIP tambahan → simpan terpisah
            if (!empty($this->fileLIP)) {
                $lipFilename = 'LIP.pdf';
                $lipPath = $outputDir . $lipFilename;

                // Simpan LIP pakai stream ke disk shared
                Storage::disk('shared')->putFileAs($outputDir, $this->fileLIP, $lipFilename);

                ClaimDocument::create([
                    'bpjs_claims_id' => $claim->id,
                    'filename' => $lipFilename,
                    'order' => 'LIP',
                    'disk' => 'shared',
                    'path' => $lipPath,
                ]);

                Log::info("LIP file saved", ['path' => $lipPath]);
            }
                
            // 8️⃣ Cleanup otomatis (hapus file sementara)
            $this->cleanUpAfterSubmit($pdfMergeService);

            // 9️⃣ Jalankan backup file secara asynchronous
            BackupFileJob::dispatch($finalPath, $lipPath ?? null);

            // 9️⃣ Tampilkan notifikasi sukses
            LivewireAlert::title('Klaim berhasil dibuat!')
                 ->text('Dokumen digabung dan backup tersimpan otomatis.')
                 ->success()
                 ->show();

        } catch (\Throwable $e) {
            Log::error("BPJS Claim Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            LivewireAlert::title('Klaim gagal dibuat!')
                ->error()
                ->text('Terjadi kegagalan saat penyimpanan file: ' . $e->getMessage())
                ->timer(2500)
                ->show();
        }
    }



    protected function createClaimRecord(): BpjsClaim
    {
        return BpjsClaim::create([
            'no_rm' => $this->medical_record_number,
            'no_kartu_bpjs' => $this->bpjs_serial_number,
            'no_sep' => $this->sep_number,
            'jenis_rawatan' => $this->jenis_rawatan,
            'tanggal_rawatan' => $this->sep_date,
            'nama_pasien' => $this->patient_name,
            'kelas_rawatan' => $this->patient_class,
        ]);
    }

    protected function storeClaimDocuments(BpjsClaim $claim, $finalPath)
    {
        Storage::disk('public')->makeDirectory('raw-documents');

        foreach ($this->scanned_docs as $index => $file) {
            if (!$file || $index === 'fileLIP') continue;

            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $tempPath = $this->rotatedPaths[$index] ?? null;

            if ($tempPath && Storage::disk('public')->exists($tempPath)) {
                $destination = 'raw-documents/'.$filename;
                Storage::disk('public')->move($tempPath, $destination);
            } else {
                // fallback
                $file->storeAs('raw-documents', $filename, 'public');
            }

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
        foreach ($this->rotatedPaths as $path) {
            if (str_starts_with($path, 'temp/') && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $pdfMergeService->cleanupTempFiles($this->rotatedPaths);
        $this->reset();
    }

    protected function showValidationErrors($validator)
    {
        foreach ($validator->errors()->all() as $error) {
            LivewireAlert::toast()
                ->error()
                ->title('Validasi Gagal')
                ->text($error)
                ->position('top-end')
                ->timer(4000)
                ->show();
        }
    }



    public function render()
    {
        return view('livewire.bpjs-rawat-jalan-form');
    }
}
