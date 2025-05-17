<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;
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
            $pdf = new Fpdi();
        
            foreach ($pdfPaths as $pdfPath) {
                // Verify file exists in storage
                if (!Storage::disk('local')->exists($pdfPath)) {
                    throw new \Exception("PDF file not found: {$pdfPath}");
                }
                $fullPath = Storage::disk('local')->path($pdfPath);
                // Get page count
                $pageCount = $pdf->setSourceFile($fullPath);
                if ($pageCount === 0) {
                    throw new \Exception("Failed to read PDF file: {$pdfPath}");
                }
                // Loop through all pages and import them
                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($templateId);
                    // Add page with proper orientation
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            }
            // Ensure output directory exists
            $outputFullPath = Storage::disk('shared')->path($outputPath);
            $outputDir = dirname($outputFullPath);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            // Save to temporary file first
            $tempPath = storage_path('app/temp_merged_'.md5(time()).'.pdf');
            $pdf->Output($tempPath, 'F');
            
            // Verify merged PDF
            if (filesize($tempPath) < 1024) {
                throw new \Exception("Merged PDF is too small - likely corrupt");
            }
            
            // Move to final location
            Storage::disk('shared')->put($outputPath, file_get_contents($tempPath));
            unlink($tempPath);
            
        return $outputPath;
        
        }catch (\Exception $e) {
           // Log detailed error
            Log::error('PDF Merge Failed', [
                'error' => $e->getMessage(),
                'files' => $pdfPaths,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Failed to merge PDFs: " . $e->getMessage());
        }
     
    }
  
}
