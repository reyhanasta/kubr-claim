<?php

use App\Services\PdfMergerService;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

beforeEach(function () {
    Storage::fake('shared');

    // Buat 2 file PDF dummy yang valid minimal (header + EOF)
    $this->pdf1 = storage_path('app/test_pdf1.pdf');
    $this->pdf2 = storage_path('app/test_pdf2.pdf');
    file_put_contents($this->pdf1, "%PDF-1.4\n%Dummy PDF 1\n%%EOF");
    file_put_contents($this->pdf2, "%PDF-1.4\n%Dummy PDF 2\n%%EOF");

    $this->outputPath = storage_path('app/test_merged_output.pdf');
    $this->service = new PdfMergerService();
});

afterEach(function () {
@unlink($this->pdf1);
@unlink($this->pdf2);
@unlink($this->outputPath);
});

it('can merge multiple PDFs successfully', function () {
    $result = $this->service->mergePdfs([$this->pdf1, $this->pdf2], $this->outputPath);

    expect(file_exists($result))->toBeTrue()
        ->and(pathinfo($result, PATHINFO_EXTENSION))->toBe('pdf');
});

it('throws exception when given empty file array', function () {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Tidak ada file PDF untuk digabungkan');

    $this->service->mergePdfs([], $this->outputPath);
});
