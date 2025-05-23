<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\PdfToText\Pdf;
use Livewire\WithFileUploads;

class BpjsRawatJalan extends Component
{
    use WithFileUploads;
    public $scanned_docs = [];
    public $scanned_docs_count = 0;
    public $nama ;
    public $no_kartu;
    public $pdfText;
    public $no_sep;

    public function readPdf()
    {
        return $this->scanned_docs_count++;
    }

    public function getPdfTextwithSpatie($file)
    {
        $pdf = new Pdf();
        $text = $pdf::getText($file->getRealPath());
        return $text;
        
    }
    
    public function render()
    {
        if (count($this->scanned_docs) > 0) {
            $this->pdfText ='test';
        }
        return view('livewire.bpjs-rawat-jalan');
    }
}
