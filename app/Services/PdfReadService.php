<?php

namespace App\Services;

use Spatie\PdfToText\Pdf;
use Illuminate\Support\Env;
class PdfReadService
{
    /**
     * Read text from a PDF file using Spatie's PdfToText package.
     *
     * @param string $pdfPath Path to the PDF file.
     * @return string Extracted text from the PDF.
     */
    public function getPdfTextwithSpatie($file)
    {
       
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
                'sep_number' => trim($matches[1]),
                'sep_date' => trim($matches[2]),
                'bpjs_serial_number' => trim($matches[3]),
                'medical_record_number' => trim($matches[4]),
                'patient_name' => trim($matches[5])
            ];
        }
        
        throw new \Exception("Format dokumen tidak dikenali");
    }
}
