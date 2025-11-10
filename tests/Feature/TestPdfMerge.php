<?php

namespace App\Console\Commands;

use App\Services\PdfMergerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestPdfMerge extends Command
{
    protected $signature = 'test:pdf-merge';

    protected $description = 'Benchmark kecepatan merge PDF versi lama dan baru';

    public function handle(PdfMergerService $merger)
    {
        $this->info('🚀 Memulai pengujian merge PDF...');

        // Pastikan folder test tersedia
        Storage::disk('public')->makeDirectory('benchmark');
        $outputPathOld = storage_path('app/public/benchmark/result_old.pdf');
        $outputPathNew = storage_path('app/public/benchmark/result_new.pdf');

        // Dummy files (ubah sesuai path kamu)
        $files = [
            storage_path('app/public/temp/sample1.pdf'),
            storage_path('app/public/temp/sample2.pdf'),
            storage_path('app/public/temp/sample3.pdf'),
        ];

        // --- TEST VERSI LAMA ---
        $this->line("\n🧩 Versi Lama:");
        $start = microtime(true);
        $merger->mergePdfsOld($files, $outputPathOld); // Tambahkan method lama di service
        $durationOld = microtime(true) - $start;
        $memoryOld = memory_get_peak_usage(true) / 1024 / 1024;

        $this->info('⏱️ Waktu: '.round($durationOld, 3).' detik');
        $this->info('💾 Memory: '.round($memoryOld, 2).' MB');

        // --- TEST VERSI BARU ---
        $this->line("\n⚙️ Versi Refactor Baru:");
        $start = microtime(true);
        $merger->mergePdfsNew($files, $outputPathNew); // Tambahkan method baru di service
        $durationNew = microtime(true) - $start;
        $memoryNew = memory_get_peak_usage(true) / 1024 / 1024;

        $this->info('⏱️ Waktu: '.round($durationNew, 3).' detik');
        $this->info('💾 Memory: '.round($memoryNew, 2).' MB');

        // --- HASIL ---
        $diff = $durationOld - $durationNew;
        $this->line("\n📊 Hasil Akhir:");
        $this->info('Efisiensi waktu: '.round(($diff / $durationOld) * 100, 2).'% lebih cepat');
    }
}
