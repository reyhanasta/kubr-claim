<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Pdf;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use setasign\Fpdi\Fpdi;

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
        $pdftoTextPath = config('services.pdftotext.path');

        if (empty($pdftoTextPath)) {
            throw new \RuntimeException('PDFTOTEXT_PATH is not configured. Please set it in .env file.');
        }

        $text = Pdf::getText($file->getRealPath(), $pdftoTextPath);

        return $text;
    }

    public function extractPdf($text)
    {
        // Normalisasi: ubah NBSP jadi space, normalisasi newline, pastikan UTF-8
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        $text = preg_replace('/\x{00A0}/u', ' ', $text); // NBSP -> space
        $text = str_replace(["\r\n", "\r"], "\n", $text); // unify newlines

        $data = [];

        // 🔹 FAIL FAST: No.SEP - Field WAJIB
        if (! preg_match('/No\.SEP\s*(?:\s*):\s*([\w\/\.-]+)/i', $text, $m)) {
            throw new \RuntimeException('No. SEP tidak ditemukan di file. Pastikan file SEP yang valid.');
        }
        
        $data['sep_number'] = trim($m[1]);

        // 🔹 FAIL FAST: Tgl.SEP - Field WAJIB
        if (! preg_match('/Tgl\.SEP\s*(?:\s*):\s*([0-9]{4}-[0-9]{2}-[0-9]{2})/i', $text, $m)) {
            throw new \RuntimeException('Tanggal SEP tidak ditemukan atau format salah. Harus YYYY-MM-DD.');
        }
        $data['sep_date'] = trim($m[1]);

        // 🔹 FAIL FAST: No.Kartu & MR - Field WAJIB
        if (! preg_match('/No\.Kartu[^\:]*:\s*([0-9]+)\s*\(\s*MR\.?\s*([0-9]+)\s*\)/i', $text, $m)) {
            throw new \RuntimeException('No. Kartu BPJS atau No. RM tidak ditemukan. Format: No.Kartu : 0001234567890 (MR. 123456)');
        }
        $data['bpjs_serial_number'] = trim($m[1]);
        $data['medical_record_number'] = trim($m[2]);

        // 🔹 FAIL FAST: Nama Peserta - Field WAJIB
        $namaFound = false;
        if (preg_match('/Nama\s*Peserta\s*(?:\s*):\s*([^\n\r]+)/i', $text, $m)) {
            $nama = trim($m[1]);
            // Bersihkan jika ada format "No.Kartu : xxx (MR. xxx) : Nama"
            if (preg_match('/.*\)\s*:\s*(.+)$/i', $nama, $cleanMatch)) {
                $data['patient_name'] = trim($cleanMatch[1]);
                $namaFound = true;
            } elseif (! empty($nama)) {
                $data['patient_name'] = $nama;
                $namaFound = true;
            }
        }

        // Alternatif: cari setelah (MR. xxx) : Nama
        if (! $namaFound && preg_match('/\(MR\.?\s*[0-9]+\)\s*:\s*([^\n\r]+)/i', $text, $m)) {
            $data['patient_name'] = trim($m[1]);
            $namaFound = true;
        }

        if (! $namaFound || empty($data['patient_name'])) {
            throw new \RuntimeException('Nama Peserta tidak ditemukan di file SEP.');
        }

        // 🔹 FAIL FAST: Kelas Rawat - Field WAJIB
        if (! preg_match('/\bKelas\s+[0-9A-Za-z]+\b/i', $text, $m)) {
            throw new \RuntimeException('Kelas Rawat tidak ditemukan. Harus ada "Kelas 1", "Kelas 2", atau "Kelas 3".');
        }
        $data['patient_class'] = trim($m[0]);

        // 🔹 FAIL FAST: Jenis Rawat - Field WAJIB
        $jenisFound = false;
        if (preg_match('/Jns\.?\s*Rawat\s*[:\-]?\s*([R\. ]?(Jalan|Inap))/i', $text, $m)) {
            $rawat = strtoupper(trim($m[1]));

            if (strpos($rawat, 'INAP') !== false) {
                $data['jenis_rawatan'] = 'RI';
                $jenisFound = true;
            } elseif (strpos($rawat, 'JALAN') !== false) {
                $data['jenis_rawatan'] = 'RJ';
                $jenisFound = true;
            }
        }

        // Fallback: cari "R.Jalan" / "R.Inap" di teks
        if (! $jenisFound) {
            if (preg_match('/R\.?\s*JALAN/i', $text)) {
                $data['jenis_rawatan'] = 'RJ';
                $jenisFound = true;
            } elseif (preg_match('/R\.?\s*INAP/i', $text)) {
                $data['jenis_rawatan'] = 'RI';
                $jenisFound = true;
            }
        }

        if (! $jenisFound) {
            throw new \RuntimeException('Jenis Rawat tidak ditemukan. Harus ada "R.Jalan" atau "R.Inap".');
        }

        Log::info('SEP berhasil diekstrak', $data);

        return $data;
    }

    /**
     * Ensure the uploaded PDF only contains a single page.
     *
     * @throws \RuntimeException when PDF has more than one page or cannot be parsed
     */
    public function ensureSinglePage(TemporaryUploadedFile $file): void
    {
        $fpdi = new Fpdi();
        $pageCount = $fpdi->setSourceFile($file->getRealPath());

        if ($pageCount !== 1) {
            throw new \RuntimeException('File SEP harus hanya 1 halaman');
        }
    }
}
