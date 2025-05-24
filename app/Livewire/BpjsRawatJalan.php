<?php

namespace App\Livewire;

use Illuminate\Support\Env;
use Livewire\Component;
use Spatie\PdfToText\Pdf;
use Livewire\WithFileUploads;

class BpjsRawatJalan extends Component
{
    use WithFileUploads;
    public $scanned_docs = [];
    public $scanned_docs_count = 0;
    public $nama ;
    public $tgl_sep;
    public $no_kartu;
    public $no_mr;
    public $pdfText;
    public $no_sep;

    public function readPdf()
    {
        return $this->scanned_docs_count++;
    }

    public function getPdfTextwithSpatie($file)
    {
        //     $text = (new Pdf(Env::get('PDFTOTEXT_PATH')))
        // ->setPdf($file->getRealPath())
        // ->text();
        $text = Pdf::getText($file->getRealPath(), Env::get('PDFTOTEXT_PATH'));
        return $text;
    }

    public function extractPdf($text){
         $pattern = '/
            No\.SEP\s*:\s*(\S+)\s+
            Tgl\.SEP\s*:\s*([0-9-]+)\s+
            Peserta\s+
            No\.Kartu\s*:\s*(\d+)\s*\(\s*MR\.?\s*(\d+)\s*\)\s+
            Nama\sPeserta\s*:\s*([^\n]+)
        /x';  // x flag for readability
        
        if (preg_match($pattern, $text, $matches)) {
            return [
                'no_sep' => trim($matches[1]),
                'tgl_sep' => trim($matches[2]),
                'no_kartu' => trim($matches[3]),
                'no_mr' => trim($matches[4]),
                'nama' => trim($matches[5])
            ];
        }
        
        throw new \Exception("Format dokumen tidak dikenali");
    }
    
    public function render()
    {
        if (count($this->scanned_docs) > 0) {
            $this->pdfText =$this->getPdfTextwithSpatie($this->scanned_docs[0]);
            $data = $this->extractPdf($this->pdfText);
            $this->fill($data);
        }
        return view('livewire.bpjs-rawat-jalan');
    }
}
