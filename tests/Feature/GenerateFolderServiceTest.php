<?php

use App\Services\GenerateFolderService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('shared');
    $this->service = new GenerateFolderService();
});

it('creates correct folder structure based on date and type', function () {
    $date = '2025-10-06';
    $sep = '1234567890';
    $jenis = 'RJ';

    $result = $this->service->generateOutputPath($date, $sep, $jenis);

    // Folder hasil biasanya seperti: 2025/10 OKTOBER RJ/1234567890/
    expect($result)->toContain('2025');
    expect($result)->toContain('R.JALAN');
    expect($result)->toContain('1234567890');

    // Simulasikan bahwa folder benar-benar dibuat di storage fake
    Storage::disk('shared')->makeDirectory($result);
    expect(Storage::disk('shared')->exists($result))->toBeTrue();
});

it('throws exception for invalid date format', function () {
    $this->service->generateOutputPath('invalid-date', 'SEP001', 'RJ');
})->throws(Exception::class);
