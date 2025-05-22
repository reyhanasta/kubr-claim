<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

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
    public function mergePdfs(array $pdfPaths, string $outputPath): string
    {
        try {
            // Validasi input
            if (empty($pdfPaths)) {
                throw new \Exception("Tidak ada file PDF untuk digabungkan");
            }

            $pdf = new Fpdi();
            $processedFiles = 0;

            foreach ($pdfPaths as $index => $pdfPath) {
                try {
                    // Pastikan path adalah string dan bukan objek UploadedFile
                    if (is_object($pdfPath)) {
                        Log::warning("Received object instead of path at index {$index}", [
                            'type' => get_class($pdfPath)
                        ]);
                        continue;
                    }

                    // Buat full path ke file
                    $fullPath = storage_path('app/public/' . $pdfPath);
                    
                    // Cek apakah file ada
                    if (!file_exists($fullPath)) {
                        Log::warning("File tidak ditemukan: {$fullPath}");
                        continue;
                    }

                    // Cek apakah file bisa dibaca dan ukurannya valid
                    if (!is_readable($fullPath) || filesize($fullPath) < 100) {
                        Log::warning("File tidak dapat dibaca atau terlalu kecil: {$fullPath}");
                        continue;
                    }

                    Log::info("Processing PDF file", [
                        'path' => $pdfPath,
                        'full_path' => $fullPath,
                        'size' => filesize($fullPath)
                    ]);

                    // Set source file dan dapatkan jumlah halaman
                    $pageCount = $pdf->setSourceFile($fullPath);
                    
                    if ($pageCount === 0) {
                        Log::warning("Tidak ada halaman ditemukan dalam PDF: {$pdfPath}");
                        continue;
                    }

                    // Import setiap halaman
                    for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                        try {
                            $templateId = $pdf->importPage($pageNumber);
                            $size = $pdf->getTemplateSize($templateId);
                            
                            // Tentukan orientasi berdasarkan ukuran
                            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                            
                            // Tambah halaman baru dengan ukuran yang sesuai
                            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                            $pdf->useTemplate($templateId);
                            
                        } catch (\Exception $pageError) {
                            Log::error("Error processing page {$pageNumber} from {$pdfPath}", [
                                'error' => $pageError->getMessage()
                            ]);
                            // Lanjutkan ke halaman berikutnya
                            continue;
                        }
                    }

                    $processedFiles++;
                    Log::info("Successfully processed PDF", [
                        'file' => $pdfPath,
                        'pages' => $pageCount
                    ]);

                } catch (\Exception $fileError) {
                    Log::error("Error processing file {$pdfPath}", [
                        'error' => $fileError->getMessage(),
                        'trace' => $fileError->getTraceAsString()
                    ]);
                    // Lanjutkan ke file berikutnya
                    continue;
                }
            }

            // Cek apakah ada file yang berhasil diproses
            if ($processedFiles === 0) {
                throw new \Exception("Tidak ada file PDF yang berhasil diproses");
            }

            // Pastikan direktori output ada di shared storage
            $outputDir = dirname($outputPath);
            if ($outputDir !== '.' && !Storage::disk('shared')->exists($outputDir)) {
                if (!Storage::disk('shared')->makeDirectory($outputDir)) {
                    throw new \Exception("Gagal membuat direktori output di shared storage: {$outputDir}");
                }
            }

            // Simpan PDF hasil merge ke file sementara dulu
            $tempPath = storage_path('app/temp_merged_' . uniqid() . '.pdf');
            $pdf->Output($tempPath, 'F');

            // Validasi hasil merge
            if (!file_exists($tempPath) || filesize($tempPath) < 1024) {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                throw new \Exception("PDF hasil merge terlalu kecil atau gagal dibuat");
            }

            // Simpan ke shared storage dengan retry mechanism
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
                'processed_files' => $processedFiles,
                'shared_storage_path' => Storage::disk('shared')->path($outputPath),
                'final_size' => Storage::disk('shared')->size($outputPath)
            ]);

            return $outputPath;

        } catch (\Exception $e) {
            Log::error('Gagal menggabungkan PDF', [
                'error' => $e->getMessage(),
                'input_files' => $pdfPaths,
                'output_path' => $outputPath,
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception("Gagal menggabungkan PDF: " . $e->getMessage());
        }
    }

    /**
     * Validate PDF file
     *
     * @param string $pdfPath
     * @return bool
     */
    private function validatePdfFile(string $pdfPath): bool
    {
        $fullPath = storage_path('app/public/' . $pdfPath);
        
        if (!file_exists($fullPath)) {
            return false;
        }

        if (!is_readable($fullPath)) {
            return false;
        }

        if (filesize($fullPath) < 100) {
            return false;
        }

        // Cek header PDF
        $handle = fopen($fullPath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 4);
        fclose($handle);

        return $header === '%PDF';
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
