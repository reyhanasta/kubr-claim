<?php

require __DIR__.'/vendor/autoload.php';

use App\Services\PdfMergerService;
use Illuminate\Support\Facades\Storage;

echo "Testing PDF merge with dummy files...\n\n";

// Copy dummy files to temp for testing
$dummyDir = 'public/dummy-pdfs';
$testFiles = [
    'SEP-DUMMY.pdf',
    'RESUME-DUMMY.pdf',
    'BILLING-DUMMY.pdf',
    'LAB-1-DUMMY.pdf',
    'LAB-2-DUMMY.pdf',
];

$tempPaths = [];
foreach ($testFiles as $file) {
    $source = storage_path("app/{$dummyDir}/{$file}");
    $tempPath = "temp/test_{$file}";
    
    if (file_exists($source)) {
        Storage::disk('public')->put($tempPath, file_get_contents($source));
        $tempPaths[] = $tempPath;
        echo "✓ Copied {$file} to temp\n";
    } else {
        echo "✗ File not found: {$file}\n";
    }
}

echo "\nAttempting to merge ".count($tempPaths)." PDF files...\n";

try {
    $merger = app(PdfMergerService::class);
    $outputPath = 'test-merged/TEST_MERGED.pdf';
    
    $result = $merger->mergePdfs($tempPaths, $outputPath);
    
    echo "\n✓ SUCCESS! PDF merged successfully!\n";
    echo "Output: {$result}\n";
    
    // Check file size
    $disk = Storage::disk('shared');
    if ($disk->exists($result)) {
        $size = $disk->size($result);
        echo "File size: ".number_format($size)." bytes (".round($size/1024, 2)." KB)\n";
    }
    
    // Cleanup temp files
    foreach ($tempPaths as $path) {
        Storage::disk('public')->delete($path);
    }
    echo "\n✓ Temp files cleaned up\n";
    
} catch (\Exception $e) {
    echo "\n✗ FAILED: ".$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
}
