<?php

namespace App\Jobs;

use App\Models\BackupLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60; // Retry after 60 seconds

    protected string $finalPath;

    protected ?string $lipPath;

    protected ?int $claimId;

    /**
     * Membuat instance job baru.
     */
    public function __construct(string $finalPath, ?string $lipPath = null, ?int $claimId = null)
    {
        $this->finalPath = $finalPath;
        $this->lipPath = $lipPath;
        $this->claimId = $claimId;
    }

    /**
     * Jalankan proses backup di background.
     */
    public function handle(): void
    {
        // Disk tujuan backup
        $backupDisk = Storage::disk('backup');
        $sharedDisk = Storage::disk('shared');

        // Backup menggunakan struktur folder yang sama dengan file asli
        // File path sudah termasuk struktur: YYYY/MM_MONTHNAME REGULER YYYY/R.JALAN/DD/SEP_NUMBER/filename

        // Backup file utama (merged PDF)
        $this->backupFile($sharedDisk, $backupDisk, $this->finalPath, 'merged');

        // Backup file LIP jika ada
        if ($this->lipPath) {
            $this->backupFile($sharedDisk, $backupDisk, $this->lipPath, 'lip');
        }
    }

    /**
     * Backup single file dengan logging.
     * Menggunakan path yang sama dengan file asli untuk menjaga struktur folder.
     */
    protected function backupFile($sourceDisk, $backupDisk, string $sourcePath, string $fileType): void
    {
        // Create backup log entry
        $backupLog = BackupLog::create([
            'bpjs_claims_id' => $this->claimId,
            'source_path' => $sourcePath,
            'file_type' => $fileType,
            'status' => 'pending',
        ]);

        try {
            // Check if source file exists
            if (!$sourceDisk->exists($sourcePath)) {
                throw new \RuntimeException("Source file not found: {$sourcePath}");
            }

            // Check if backup disk is writable
            if (!$this->isBackupDiskAccessible($backupDisk)) {
                throw new \RuntimeException("Backup disk is not accessible or writable");
            }

            // Gunakan path yang sama dengan file asli
            // Struktur: YYYY/MM_MONTHNAME REGULER YYYY/R.JALAN/DD/SEP_NUMBER/filename
            $backupPath = $sourcePath;

            // Perform backup
            $backupDisk->put($backupPath, $sourceDisk->get($sourcePath));

            // Get file size
            $fileSize = $backupDisk->size($backupPath);

            // Mark as success
            $backupLog->markAsSuccess($backupPath, $fileSize);

            Log::info('Backup berhasil', [
                'source' => $sourcePath,
                'backup' => $backupPath,
                'size' => $fileSize,
                'type' => $fileType,
            ]);
        } catch (\Throwable $e) {
            $backupLog->markAsFailed($e->getMessage());

            Log::error('Backup gagal', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
                'type' => $fileType,
                'attempt' => $this->attempts(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Check if backup disk is accessible.
     */
    protected function isBackupDiskAccessible($disk): bool
    {
        try {
            $testFile = '.backup_test_' . uniqid();
            $disk->put($testFile, 'test');
            $disk->delete($testFile);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Handle job failure after all retries.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Backup job failed permanently', [
            'finalPath' => $this->finalPath,
            'lipPath' => $this->lipPath,
            'claimId' => $this->claimId,
            'error' => $exception->getMessage(),
        ]);

        // Update any pending logs for this job to failed
        BackupLog::where('bpjs_claims_id', $this->claimId)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'error_message' => 'Job failed after max retries: ' . $exception->getMessage(),
            ]);

        // TODO: Send notification (email/slack/etc)
        // Notification::send(User::admins(), new BackupFailedNotification($this->claimId, $exception->getMessage()));
    }
}
