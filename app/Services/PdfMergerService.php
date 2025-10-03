<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PdfMergerService 
{
    protected int $minFileSize = 100; // bytes
    protected int $maxRetries   = 3;

    /**
     * Merge multiple PDF files into one.
     *
     * @param array $pdfPaths
     * @param string $outputPath
     * @return string
     * @throws \Exception
     */
    public function mergePdfs(array $pdfPaths, string $outputPath): string
    {
        $startTime = microtime(true);
        $correlationId = (string) Str::uuid();

        Log::info('PDF merge process started', [
            'correlation_id' => $correlationId,
            'output_path'    => $outputPath,
            'file_count'     => count($pdfPaths),
        ]);

        if (empty($pdfPaths)) {
            throw new \Exception("Tidak ada file PDF untuk digabungkan");
        }

        $pdf = new Fpdi();
        $processedFiles = $this->processPdfFiles($pdf, $pdfPaths);

        if ($processedFiles === 0) {
            throw new \Exception("Tidak ada file PDF yang berhasil diproses");
        }

        $this->makeDirectory($outputPath);

        $tempPath = storage_path('app/temp_merged_' . uniqid() . '.pdf');
        $pdf->Output($tempPath, 'F');

        $this->validateMergedFile($tempPath);

        $this->saveToSharedStorage($tempPath, $outputPath);

        $duration = round(microtime(true) - $startTime, 3);

        Log::info('PDF merge completed successfully', [
            'correlation_id' => $correlationId,
            'output_path'    => $outputPath,
            'processed_files'=> $processedFiles,
            'duration_sec'   => $duration,
        ]);

        return $outputPath;
    }

    /**
     * Process each PDF and add pages into $pdf.
     */
    protected function processPdfFiles(Fpdi $pdf, array $pdfPaths): int
    {
        $processed = 0;

        foreach ($pdfPaths as $path) {
            if (!Storage::disk('public')->exists($path)) {
                Log::warning("File tidak ditemukan: {$path}");
                continue;
            }

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
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpage);
                }
                $processed++;
                Log::debug("File berhasil diproses: {$path}");
            } catch (\Exception $e) {
                Log::error("Gagal memproses file: {$path}", ['error' => $e->getMessage()]);
            }
        }

        return $processed;
    }

    /**
     * Make sure output directory exists.
     */
    protected function makeDirectory(string $path): void
    {
        $directory = dirname($path);
        Storage::disk('shared')->makeDirectory($directory);
    }

    /**
     * Validate merged PDF file.
     */
    protected function validateMergedFile(string $tempPath): void
    {
        if (!file_exists($tempPath)) {
            throw new \Exception("File hasil merge tidak ditemukan");
        }

        if (filesize($tempPath) < 1024) {
            $this->cleanupTempFile($tempPath);
            throw new \Exception("PDF hasil merge terlalu kecil atau gagal dibuat");
        }
    }

    /**
     * Save merged file to shared storage with retries.
     */
    protected function saveToSharedStorage(string $tempPath, string $outputPath): void
    {
        $retry = 0;
        $saved = false;

        while ($retry < $this->maxRetries && !$saved) {
            try {
                $content = file_get_contents($tempPath);
                if (Storage::disk('shared')->put($outputPath, $content)) {
                    $saved = true;
                } else {
                    throw new \Exception("Gagal menulis ke shared storage");
                }
            } catch (\Exception $e) {
                $retry++;
                Log::warning("Gagal simpan ke shared storage (percobaan {$retry})", [
                    'error' => $e->getMessage(),
                    'path'  => $outputPath,
                ]);
                sleep(1);
            }
        }

        $this->cleanupTempFile($tempPath);

        if (!$saved) {
            throw new \Exception("Gagal menyimpan file ke shared storage setelah {$this->maxRetries} percobaan");
        }
    }

    /**
     * Cleanup a single temp file.
     */
    protected function cleanupTempFile(string $tempPath): void
    {
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    /**
     * Cleanup temp files in public storage.
     */
    public function cleanupTempFiles(array $tempPaths): void
    {
        foreach ($tempPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::debug("Cleaned up temp file: {$path}");
            }
        }
    }
}
