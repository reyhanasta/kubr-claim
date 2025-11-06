<?php

namespace App\Jobs;

use App\Models\ClaimDocument;
use App\Services\GenerateFolderService;
use App\Services\PdfMergerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBpjsDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scannedDocs;

    protected $rotations;

    protected $sepDate;

    protected $sepNumber;

    protected $patientName;

    protected $claimId;

    public function __construct($scannedDocs, $rotations, $sepDate, $sepNumber, $patientName, $claimId)
    {
        $this->scannedDocs = $scannedDocs;
        $this->rotations = $rotations;
        $this->sepDate = $sepDate;
        $this->sepNumber = $sepNumber;
        $this->patientName = $patientName;
        $this->claimId = $claimId;
    }

    public function handle(PdfMergerService $pdfMerger, GenerateFolderService $folderService)
    {
        Log::info("Queue: Processing claim ID {$this->claimId}");

        // Generate folder
        $outputPath = $folderService->generateOutputPath($this->sepDate, $this->sepNumber, $this->patientName);

        // Merge PDFs (apply rotations if needed)
        // $finalPath = $pdfMerger->mergePdfs($this->scannedDocs, $outputPath, $this->rotations);
        $finalPath = $pdfMerger->mergePdfs($this->scannedDocs, $outputPath);

        // Save documents info
        foreach ($this->scannedDocs as $index => $filePath) {
            ClaimDocument::create([
                'bpjs_claims_id' => $this->claimId,
                'filename' => basename($filePath),
                'order' => $index,
                'disk' => $finalPath,
            ]);
        }

        Log::info("Queue: Completed claim ID {$this->claimId}");
    }
}
