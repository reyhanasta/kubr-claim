<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Libraries\RotatableFPDI;
use Illuminate\Support\Facades\Storage;


class PdfMergerService {
    /**
     * Merge multiple PDF files into one.
     *
     * @param array $pdfPaths Array of paths to the PDF files to merge.
     * @param string $outputPath Path where the merged PDF will be saved.
     * @return string Path to the merged PDF file.
     * @throws \Exception
     */

    public function mergePdfs(array $pdfPaths, string $outputPath): string
    {
        try {
            $pdf = new RotatableFPDI();

            foreach ($pdfPaths as $pdfPath) {
                $fullPath = Storage::disk('public')->exists($pdfPath)
                    ? Storage::disk('public')->path($pdfPath)
                    : storage_path('app/public/' . $pdfPath); // fallback

                $pageCount = $pdf->setSourceFile($fullPath);
                if ($pageCount === 0) {
                    throw new \Exception("Gagal membaca halaman dari PDF: {$pdfPath}");
                }

                for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                    $templateId = $pdf->importPage($pageNumber);
                    $size = $pdf->getTemplateSize($templateId);
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            }

            // Simpan ke file sementara
            $outputFullPath = Storage::disk('shared')->path($outputPath);
            $outputDir = dirname($outputFullPath);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $tempPath = storage_path('app/temp_merged_' . md5(time()) . '.pdf');
            $pdf->Output($tempPath, 'F');

            if (filesize($tempPath) < 1024) {
                unlink($tempPath);
                throw new \Exception("PDF hasil terlalu kecil — kemungkinan gagal saat merge.");
            }

            Storage::disk('shared')->put($outputPath, file_get_contents($tempPath));
            unlink($tempPath);

            return $outputPath;

        } catch (\Exception $e) {
            Log::error('Gagal Menggabungkan PDF', [
                'error' => $e->getMessage(),
                'files' => $pdfPaths,
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception("Gagal menggabungkan PDF: " . $e->getMessage());
        }
    }

     
}
