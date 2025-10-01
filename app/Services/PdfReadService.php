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

    // public function extractPdf($text){
    //      $pattern = '/
    //         No\.SEP\s*:\s*(\S+)\s+
    //         Tgl\.SEP\s*:\s*([0-9-]+)\s+
    //         Peserta\s+
    //         No\.Kartu\s*:\s*(\d+)\s*\(\s*MR\.?\s*(\d+)\s*\)\s+
    //         Nama\sPeserta\s*:\s*([^\n]+)
            
    //     /xs';  // x flag for readability
       
    //     if (preg_match($pattern, $text, $matches)) {
    //         return [
    //             'sep_number' => trim($matches[1]),
    //             'sep_date' => trim($matches[2]),
    //             'bpjs_serial_number' => trim($matches[3]),
    //             'medical_record_number' => trim($matches[4]),
    //             'patient_name' => trim($matches[5])
    //         ];
    //     }
    //     return null;
    // }

    public function extractPdf($text){
        // Normalisasi: ubah NBSP jadi space, normalisasi newline, pastikan UTF-8
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        $text = preg_replace('/\x{00A0}/u', ' ', $text); // NBSP -> space
        $text = str_replace(["\r\n", "\r"], "\n", $text); // unify newlines

        $data = [];

        // No.SEP (boleh ada spasi / newline antara label dan ':')
        if (preg_match('/No\.SEP\s*(?:\s*):\s*([\w\/\.-]+)/i', $text, $m)) {
            $data['sep_number'] = trim($m[1]);
        }

        // Tgl.SEP (YYYY-MM-DD)
        if (preg_match('/Tgl\.SEP\s*(?:\s*):\s*([0-9]{4}-[0-9]{2}-[0-9]{2})/i', $text, $m)) {
            $data['sep_date'] = trim($m[1]);
        }

        // No.Kartu (mendukung jika ':' di baris berikutnya)
        if (preg_match('/No\.Kartu\s*(?:\s*):\s*([0-9]+)\s*\(\s*MR\.?\s*([0-9]+)\s*\)/i', $text, $m)) {
            $data['bpjs_serial_number']    = trim($m[1]);
            $data['medical_record_number'] = trim($m[2]);
        }

        // Nama Peserta (toleran terhadap newline sebelum ':')
        if (preg_match('/Nama\s*Peserta\s*(?:\s*):\s*([^\n\r]+)/i', $text, $m)) {
            $data['patient_name'] = trim($m[1]);
        }

        // Kls.Hak — pola lebih fleksibel:
        // - boleh "Kls.Hak" atau "Kls. Hak" atau "Kls Hak"
        // - mengizinkan sebanyak apapun whitespace/newline sebelum ':' atau setelahnya
        // if (preg_match('/Kls\.?\s*Hak\s*(?:\s*):\s*([^\n\r]+)/i', $text, $m)) {
        //     $data['patient_class'] = trim($m[1]);
        // } else {
        //     // fallback: kadang ada "Kls.Rawat" atau label lain yang berisi kata 'Kelas'
        //     if (preg_match('/Kls\.?\s*Rawat\s*(?:\s*):\s*([^\n\r]+)/i', $text, $m)) {
        //         $data['treatment_class'] = trim($m[1]);
        //     } elseif (preg_match('/\bKelas\s+[0-9A-Za-z]+\b/i', $text, $m)) {
        //         // fallback kasar: cari kata "Kelas 3" di mana saja
        //         $data['patient_class_fallback'] = trim($m[0]);
        //     }
        // }
        if (preg_match('/\bKelas\s+[0-9A-Za-z]+\b/i', $text, $m)) {
                // fallback kasar: cari kata "Kelas 3" di mana saja
                $data['patient_class'] = trim($m[0]);
            }

        if (preg_match('/Jns\.?\s*Rawat\s*\n\s*:\s*([^\n\r]+)/i', $text, $m)) {
            $data['care_type'] = trim($m[1]); // hasil: R.Jalan
        }

        preg_match_all('/Jns[^\n]{0,50}/', $text, $matches);
print_r($matches);


        return $data ?: null;
    }



}
