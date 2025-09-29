<?php

namespace App\Services;


use Illuminate\Support\Str;


class GenerateFolderService 
{
    
    public function generateOutputPath(string $sep_date,$sep_number,$patient_name): string
    {
         // Set the locale to Indonesian
        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($sep_date); // Use user-provided date
        $month = $date->format('m') ." ". strtoupper($date->translatedFormat('F')); // e.g., "April 2025"
        $year = $date->format('Y'); // e.g., "April 2025"
        $day = $date->format('d'); // "4"

        // Use the user-provided date for the folder structure
        $jenisRawatan = 'RJ'; // Default to 'RJ' for Rawat Jalan

        $folderPath =  "{$month} REGULER {$year}/{$jenisRawatan}/{$day}/{$sep_number}";
        $patientName = trim(explode(',', $patient_name)[0]);
        return "bpjs-claims/{$folderPath}/" . Str::upper($patientName) . '.pdf';
    }
}
