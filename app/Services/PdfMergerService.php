<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class PdfMergerService
{
    protected int $minFileSize = 100; // bytes

    protected int $maxRetries = 3;

    // protected ?PdfDecompressionService $decompressor = null;

    // public function __construct(?PdfDecompressionService $decompressor = null)
    // {
    //     $this->decompressor = $decompressor;
    // }

    /**
     * Merge multiple PDF files into one.
     *
     * @throws \Exception
     */
    public function mergePdfs(array $pdfPaths, string $outputPath): string
    {
        $startTime = microtime(true);
        $correlationId = (string) Str::uuid();

        Log::info('PDF merge process started', [
            'correlation_id' => $correlationId,
            'output_path' => $outputPath,
            'file_count' => count($pdfPaths),
        ]);

        if (empty($pdfPaths)) {
            throw new \Exception('Tidak ada file PDF untuk digabungkan');
        }

        $pdf = new Fpdi;
        $processedFiles = $this->processPdfFiles($pdf, $pdfPaths);

        if ($processedFiles === 0) {
            throw new \Exception('Tidak ada file PDF yang berhasil diproses');
        }

        $this->makeDirectory($outputPath);

        $tempPath = storage_path('app/temp_merged_'.uniqid().'.pdf');
        $pdf->Output($tempPath, 'F');

        $this->validateMergedFile($tempPath);

        $this->saveToSharedStorage($tempPath, $outputPath);

        $duration = round(microtime(true) - $startTime, 3);

        Log::info('PDF merge completed successfully', [
            'correlation_id' => $correlationId,
            'output_path' => $outputPath,
            'processed_files' => $processedFiles,
            'duration_sec' => $duration,
        ]);

        return $outputPath;
    }

    /**
     * Process each PDF and add pages into $pdf.
     */
    protected function processPdfFiles(Fpdi $pdf, array $pdfPaths): int
    {
        $processed = 0;
        $disk = Storage::disk('public');

        foreach ($pdfPaths as $path) {
            if (! $disk->exists($path)) {
                Log::warning("File tidak ditemukan: {$path}");
                continue;
            }

            $filePath = $disk->path($path);
            $size = filesize($filePath);

            if ($size < $this->minFileSize) {
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
            } catch (\Exception $e) {
                // If compression error, try to decompress PDF and retry
                
                Log::error("Gagal memproses file: {$path}", ['error' => $e->getMessage()]);
                throw $e;
                
            }
        }
        
        return $processed;


    }

    protected function saveToSharedStorage(string $tempPath, string $outputPath): void
    {
        $disk = Storage::disk('shared');
        $retry = 0;
        $saved = false;

        while ($retry < $this->maxRetries && ! $saved) {
            try {
                $stream = fopen($tempPath, 'r');
                if ($disk->put($outputPath, $stream)) {
                    $saved = true;
                } else {
                    throw new \Exception('Gagal menulis ke shared storage');
                }
            } catch (\Exception $e) {
                $retry++;
                Log::warning("Retry save shared storage ke-{$retry}", ['error' => $e->getMessage()]);
                usleep(200000); // 0.2s delay
            }
        }

        $this->cleanupTempFile($tempPath);

        if (! $saved) {
            throw new \Exception("Gagal menyimpan file ke shared storage setelah {$this->maxRetries} percobaan");
        }
    }

    protected function cleanupTempFile(string $tempPath): void
    {
        if (Storage::exists($tempPath)) {
            Storage::delete($tempPath);
            Log::debug("Temp file dibersihkan: {$tempPath}");
        }
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
        if (! file_exists($tempPath)) {
            throw new \Exception('File hasil merge tidak ditemukan');
        }

        if (filesize($tempPath) < 1024) {
            $this->cleanupTempFile($tempPath);
            throw new \Exception('PDF hasil merge terlalu kecil atau gagal dibuat');
        }
    }

    // TESTIN PURPOSE ONLY - REMOVE IN PRODUCTION
    public function mergePdfsOld(array $files, string $outputPath)
    {
        // logika lama kamu di sini (copy-paste dari kode sebelumnya)
        return $this->mergePdfs($files, $outputPath);
    }

    public function mergePdfsNew(array $files, string $outputPath)
    {
        // versi refactor yang aku bantu ubah
        $output = new Fpdi;

        foreach ($files as $file) {
            if (! file_exists($file)) {
                continue;
            }
            $pageCount = $output->setSourceFile($file);

            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $output->importPage($i);
                $size = $output->getTemplateSize($tpl);
                $output->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $output->useTemplate($tpl);
            }
        }

        $output->Output($outputPath, 'F');

        return $outputPath;
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
