<?php 

namespace App\Services;

use Carbon\Carbon;

class GenerateFolderService
{
    /**
     * Generate output folder path for claim files
     *
     * @param string $sep_date  Tanggal SEP (format bebas, akan diparse Carbon)
     * @param string $sep_number Nomor SEP
     * @param string $jenisRawatan Jenis rawatan (default: RJ)
     * @return string Path folder relatif
     */
    public function generateOutputPath(string $sep_date, string $sep_number, string $jenis_rawatan = 'RJ'): string
    {
        // Set locale ke Indonesia
        Carbon::setLocale('id');

        $date  = Carbon::parse($sep_date);
        $month = $date->format('m') . ' ' . strtoupper($date->translatedFormat('F')); // contoh: "04 APRIL"
        $year  = $date->format('Y'); 
        $day   = $date->format('d'); 

        // Pastikan jenis_rawatan hanya RI atau RJ
        $jenisRawatan = strtoupper($jenis_rawatan) === 'RI' ? 'R.Inap' : 'R.Jalan';

        // Contoh struktur: 2025/04 APRIL 2025/RI/03/1234567890/
        $folderPath = "{$month} REGULER {$year}/{$jenisRawatan}/{$day}/{$sep_number}";

        return "{$year}/{$folderPath}/";
    }
}
