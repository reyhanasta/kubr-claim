<?php

use Illuminate\Support\Facades\Storage;
use App\Services\PdfMergerService;
use setasign\Fpdi\Fpdi;

beforeEach(function () {
    // Ensure clean, isolated storage
    Storage::fake('local');
    Storage::fake('shared');
});

it('merges multiple PDF files successfully', function () {
    $service = new PdfMergerService();

    // Create sample PDF files
    $pdf1Path = 'local/sample1.pdf';
    $pdf2Path = 'local/sample2.pdf';
    $outputPath = 'merged/test_merged.pdf';

    // Create dummy PDFs
    Storage::disk('local')->put($pdf1Path, generateTestPdf('Test PDF 1'));
    Storage::disk('local')->put($pdf2Path, generateTestPdf('Test PDF 2'));

    // Run the merge
    $mergedPath = $service->mergePdfs([$pdf1Path, $pdf2Path], $outputPath);

    // Verify the merged file exists
    Storage::disk('shared')->assertExists($mergedPath);

    // Verify the file is not empty
    expect(Storage::disk('shared')->size($mergedPath))->toBeGreaterThan(1024);

    // Clean up after test
    Storage::disk('shared')->delete($mergedPath);
})->group('pdf');

it('throws an exception for missing files', function () {
    $service = new PdfMergerService();

    $outputPath = 'merged/test_missing.pdf';
    
    // Attempt to merge non-existent files
    expect(fn() => $service->mergePdfs(['local/missing.pdf'], $outputPath))
        ->toThrow(Exception::class, 'PDF file not found: local/missing.pdf');
})->group('pdf');

it('throws an exception for empty PDFs', function () {
    $service = new PdfMergerService();

    $outputPath = 'merged/test_empty.pdf';
    $emptyFilePath = 'local/empty.pdf';

    // Create a minimal but empty PDF
    Storage::disk('local')->put($emptyFilePath, generateEmptyPdf());

    // Expect an exception for empty file
    expect(fn() => $service->mergePdfs([$emptyFilePath], $outputPath))
        ->toThrow(Exception::class, "Failed to merge PDFs: Merged PDF is too small - likely corrupt");
})->group('pdf');


/**
 * Generate a simple test PDF content.
 */
function generateTestPdf($text)
{
    $pdf = new Fpdi();
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', '', 16);
    $pdf->Text(10, 10, $text);
    return $pdf->Output('S');
}

/**
 * Generate a minimal valid empty PDF content.
 */
function generateEmptyPdf(): string
{
    $pdf = new Fpdi();
    $pdf->AddPage();
    return $pdf->Output('S');
}
