<?php

namespace App\Services;

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Pdf;

class PdfReadService
{
    /**
     * Read text from a PDF file using Spatie's PdfToText package.
     *
     * @param  string  $pdfPath  Path to the PDF file.
     * @return string Extracted text from the PDF.
     */
    public function getPdfTextwithSpatie($file)
    {

        $text = Pdf::getText($file->getRealPath(), Env::get('PDFTOTEXT_PATH'));

        return $text;
    }

    public function extractPdf($text)
    {
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
            $data['bpjs_serial_number'] = trim($m[1]);
            $data['medical_record_number'] = trim($m[2]);
        }

        // Nama Peserta (toleran terhadap newline sebelum ':')
        if (preg_match('/Nama\s*Peserta\s*(?:\s*):\s*([^\n\r]+)/i', $text, $m)) {
            $data['patient_name'] = trim($m[1]);
        }
        if (preg_match('/\bKelas\s+[0-9A-Za-z]+\b/i', $text, $m)) {
            // fallback kasar: cari kata "Kelas 3" di mana saja
            $data['patient_class'] = trim($m[0]);
        }
        // Jenis Rawat (Jns.Rawat)
        // Jenis Rawat (Jns.Rawat : R.Jalan / R.Inap)
        if (preg_match('/Jns\.?\s*Rawat\s*[:\-]?\s*([R\. ]?(Jalan|Inap))/i', $text, $m)) {
            $rawat = strtoupper(trim($m[1]));

            if (strpos($rawat, 'INAP') !== false) {
                $data['jenis_rawatan'] = 'RI';
            } elseif (strpos($rawat, 'JALAN') !== false) {
                $data['jenis_rawatan'] = 'RJ';
            } else {
                $data['jenis_rawatan'] = 'RJ'; // fallback
            }
        } else {
            // Coba fallback lain: cari "R.Jalan" / "R.Inap" di teks
            if (preg_match('/R\.?JALAN/i', $text)) {
                $data['jenis_rawatan'] = 'RJ';
            } elseif (preg_match('/R\.?INAP/i', $text)) {
                $data['jenis_rawatan'] = 'RI';
            } else {
                $data['jenis_rawatan'] = 'RJ'; // default
            }
        }

        // 🔹 Tambahkan debug log
        Log::debug('PDF Extracted Data:', $data);
        Log::debug('Rawat block check:', [
            'matched_text' => substr($text, strpos($text, 'Jns.Rawat'), 50), // lihat 50 karakter setelah "Jns.Rawat"
        ]);

        return $data ?: null;
    }
}
