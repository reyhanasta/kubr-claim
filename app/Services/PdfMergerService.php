<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PdfMergerService 
{
    /**
     * Merge multiple PDF files into one.
     *
     * @param array $pdfPaths Array of paths to the PDF files to merge (relative paths from public storage)
     * @param string $outputPath Path where the merged PDF will be saved (relative to public storage)
     * @return string Path to the merged PDF file (relative path)
     * @throws \Exception
     */

    protected int $minFileSize = 100;
    protected int $maxRetries = 3;
    public function mergePdfs(array $pdfPaths, string $outputPath): string{
        $logContext = [
            'correlation_id' =>  Str::uuid(),
            'output_path' => $outputPath,
            'file_count' => count($pdfPaths),
            'initiated_at' => now()->toDateTimeString()
        ];

        Log::info('PDF merge process started', $logContext);

        try {
            // 1. Input validation
            Log::debug('Validating input PDF paths');
            if (empty($pdfPaths)) {
                Log::error('Empty PDF paths array received');
                throw new \Exception("Tidak ada file PDF untuk digabungkan");
            }

            $pdf = new Fpdi();
            $processedFiles = 0;

            // 2. File processing
            Log::info('Starting PDF files processing', [
                'total_files' => count($pdfPaths)
            ]);
            $this->processPdfFiles($pdf, $pdfPaths, $processedFiles);

            // 3. Processed files check
            Log::debug('Checking processed files count', [
                'processed_files' => $processedFiles
            ]);
            if ($processedFiles === 0) {
                Log::error('No files were successfully processed', [
                    'attempted_files' => count($pdfPaths)
                ]);
                throw new \Exception("Tidak ada file PDF yang berhasil diproses");
            }

            // 4. Directory preparation
            Log::info('Preparing output directory', [
                'output_path' => $outputPath
            ]);

            $this->makeDirectory($outputPath);
            
            // 5. Temporary file creation
            $tempPath = storage_path('app/temp_merged_' . uniqid() . '.pdf');
            Log::info('Creating temporary merged file', [
                'temp_path' => $tempPath
            ]);
            $pdf->Output($tempPath, 'F');

            // 6. Merge validation
            Log::debug('Validating merged output', [
                'temp_path' => $tempPath,
                'file_exists' => file_exists($tempPath),
                'file_size' => file_exists($tempPath) ? filesize($tempPath) : 0
            ]);
            if (!file_exists($tempPath) || filesize($tempPath) < 1024) {
                Log::error('Merged PDF validation failed', [
                    'exists' => file_exists($tempPath),
                    'size' => file_exists($tempPath) ? filesize($tempPath) : 0,
                    'threshold' => 1024
                ]);
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                throw new \Exception("PDF hasil merge terlalu kecil atau gagal dibuat");
            }

            // 7. Final save operation
            Log::info('Saving to shared storage', [
                'temp_path' => $tempPath,
                'output_path' => $outputPath
            ]);

            $this->saveSharedStorage($tempPath, $outputPath);

            Log::info('PDF merge completed successfully', [
                'output_path' => $outputPath,
                'processed_files' => $processedFiles,
                'final_size' => Storage::disk('shared')->size($outputPath),
                'duration_seconds' => now()->diffInSeconds($logContext['initiated_at'])
            ]);

            return $outputPath;

        } catch (\Exception $e) {
            Log::error('PDF merge process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'failed_at' => now()->toDateTimeString(),
                'duration_seconds' => now()->diffInSeconds($logContext['initiated_at'])
            ]);
            
            if (isset($tempPath) && file_exists($tempPath)) {
                Log::debug('Cleaning up temporary file after failure', [
                    'temp_path' => $tempPath
                ]);
                unlink($tempPath);
            }
            
            throw new \Exception("Gagal menggabungkan PDF: " . $e->getMessage());
        }
    }

    /**
     * Process each PDF file and add it to the merged PDF
     *
     * @param Fpdi $pdf
     * @param array $pdfPaths
     * @param int $processedFiles
     * @return void
     */
    public function processPdfFiles(Fpdi $pdf, array $pdfPaths, int &$processedFiles): void
    {
        foreach ($pdfPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $filePath = Storage::disk('public')->path($path);
                if (filesize($filePath) < $this->minFileSize) {
                    Log::warning("File terlalu kecil untuk diproses: {$path}");
                    continue;
                }

                try {
                    $pageCount = $pdf->setSourceFile($filePath);
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $tpage = $pdf->importPage($i);
                        $size = $pdf->getTemplateSize($tpage);
                        $orientation = isset($size['orientation']) ? $size['orientation'] : ($size['width'] > $size['height'] ? 'L' : 'P');
                        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                        $pdf->useTemplate($tpage);
                    }
                    Log::info("File berhasil diproses: {$path}");
                    $processedFiles++;
                } catch (\Exception $e) {
                    Log::error("Gagal memproses file: {$path}", [
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::warning("File tidak ditemukan: {$path}");
            }
        }
       
    }

    /**
     * Check if the directory exists, if not create it
     *
     * @param string $path
     * @return void
     */
    public function makeDirectory(string $path): void
    {
        $directory = dirname($path);
        if (!Storage::disk('shared')->exists($directory)) {
            try{
                Storage::disk('shared')->makeDirectory($directory);
                Log::info("Directory created: {$directory}");
            }catch (\Exception $e) {
                throw new \Exception("Gagal membuat direktori output di shared storage: {$directory}");
            }
           
        }
    }

    /**
     * Save the merged PDF to shared storage with retry mechanism
     *
     * @param string $tempPath
     * @param string $outputPath
     * @return void
     */
    public function saveSharedStorage($tempPath, $outputPath): void{
            $maxRetries = 3;
            $retryCount = 0;
            $saved = false;
            
            while ($retryCount < $maxRetries && !$saved) {
                try {
                    $fileContent = file_get_contents($tempPath);
                    if (Storage::disk('shared')->put($outputPath, $fileContent)) {
                        $saved = true;
                    } else {
                        throw new \Exception("Failed to write to shared storage");
                    }
                } catch (\Exception $e) {
                    $retryCount++;
                    Log::warning("Attempt {$retryCount} failed to save to shared storage", [
                        'error' => $e->getMessage(),
                        'path' => $outputPath
                    ]);
                    
                    if ($retryCount < $maxRetries) {
                        sleep(1); // Wait 1 second before retry
                    }
                }
            }
            
            if (!$saved) {
                unlink($tempPath);
                throw new \Exception("Gagal menyimpan file ke shared storage setelah {$maxRetries} percobaan");
            }

            // Cleanup temp file
            unlink($tempPath);

            Log::info("PDF merge completed successfully", [
                'output_path' => $outputPath,
                'temp_path' => $tempPath,
                'shared_storage_path' => Storage::disk('shared')->path($outputPath),
                'final_size' => Storage::disk('shared')->size($outputPath)
            ]);
    }

  
    /**
     * Clean up temporary files
     *
     * @param array $tempPaths
     * @return void
     */
    public function cleanupTempFiles(array $tempPaths): void
    {
        foreach ($tempPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                try {
                    Storage::disk('public')->delete($path);
                    Log::info("Cleaned up temp file: {$path}");
                } catch (\Exception $e) {
                    Log::warning("Failed to cleanup temp file: {$path}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }
}
