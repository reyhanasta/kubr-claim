<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $finalPath;
    protected ?string $lipPath;

    /**
     * Membuat instance job baru.
     */
    public function __construct(string $finalPath, ?string $lipPath = null)
    {
        $this->finalPath = $finalPath;
        $this->lipPath = $lipPath;
    }

    /**
     * Jalankan proses backup di background.
     */
    public function handle(): void
    {
        try {
            // Tentukan direktori backup (per tahun/bulan)
            $backupDir = 'backup_claims/' . now()->format('Y/m');

            // Disk tujuan backup (bisa diganti ke s3 / ftp)
            $backupDisk = Storage::disk('backup');

            // Kumpulkan file yang akan dibackup
            $filesToBackup = [$this->finalPath];
            if ($this->lipPath) {
                $filesToBackup[] = $this->lipPath;
            }

            foreach ($filesToBackup as $file) {
                if (Storage::disk('shared')->exists($file)) {
                    $filename = basename($file);
                    $backupDisk->put(
                        $backupDir . '/' . $filename,
                        Storage::disk('shared')->get($file)
                    );
                }
            }

            Log::info('Backup klaim berhasil', [
                'paths' => $filesToBackup,
                'backupDir' => $backupDir,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Backup klaim gagal', [
                'error' => $e->getMessage(),
                'file' => $this->finalPath,
            ]);
        }
    }
}
