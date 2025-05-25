<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\PdfReadService;

class BpjsRawatJalanForm extends Component
{
    use WithFileUploads;
    public $scanned_docs = [];
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
    public $tanggal_rawatan ;
        public $previewUrls = [];
    public $fileOrder = [];
    public $rotatedPaths = [];
    public $showPreviewModal = false;
    public $currentPreviewIndex = null;

    public function pdfProcessing (PdfReadService $pdfReadService)
    {
        if (count($this->scanned_docs) > 0) {
            $this->pdfText = $pdfReadService->getPdfTextwithSpatie($this->scanned_docs[0]);
            $data = $pdfReadService->extractPdf($this->pdfText);
            $this->fill($data);
        }

    }
    public function render()
    {
        $this->pdfProcessing(new PdfReadService());
        return view('livewire.bpjs-rawat-jalan-form');
    }
}
