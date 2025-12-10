<?php

namespace App\Livewire;

use App\Models\BackupLog;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class BackupDashboard extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public string $dateFilter = 'today';

    public string $searchQuery = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function stats(): array
    {
        $baseQuery = BackupLog::query();

        // Apply date filter for stats
        $baseQuery = match ($this->dateFilter) {
            'today' => $baseQuery->whereDate('created_at', today()),
            'week' => $baseQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $baseQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'year' => $baseQuery->whereYear('created_at', now()->year),
            default => $baseQuery,
        };

        $total = (clone $baseQuery)->count();
        $success = (clone $baseQuery)->where('status', 'success')->count();
        $failed = (clone $baseQuery)->where('status', 'failed')->count();
        $pending = (clone $baseQuery)->where('status', 'pending')->count();
        $totalSize = (clone $baseQuery)->where('status', 'success')->sum('file_size');

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 1) : 0,
            'total_size' => $this->formatBytes($totalSize),
        ];
    }

    #[Computed]
    public function diskStatus(): array
    {
        try {
            $backupDisk = Storage::disk('backup');
            $backupPath = config('filesystems.disks.backup.root');

            // Check if disk is accessible
            $isAccessible = false;
            try {
                $testFile = '.health_check_'.uniqid();
                $backupDisk->put($testFile, 'test');
                $backupDisk->delete($testFile);
                $isAccessible = true;
            } catch (\Throwable $e) {
                $isAccessible = false;
            }

            // Get disk space info (only works on local disks)
            $freeSpace = null;
            $totalSpace = null;
            $usedPercentage = null;

            if ($backupPath && is_dir($backupPath)) {
                $freeSpace = disk_free_space($backupPath);
                $totalSpace = disk_total_space($backupPath);
                $usedSpace = $totalSpace - $freeSpace;
                $usedPercentage = round(($usedSpace / $totalSpace) * 100, 1);
            }

            return [
                'path' => $backupPath,
                'accessible' => $isAccessible,
                'free_space' => $freeSpace ? $this->formatBytes($freeSpace) : 'N/A',
                'total_space' => $totalSpace ? $this->formatBytes($totalSpace) : 'N/A',
                'used_percentage' => $usedPercentage ?? 0,
                'status' => $isAccessible ? 'healthy' : 'error',
            ];
        } catch (\Throwable $e) {
            return [
                'path' => 'Unknown',
                'accessible' => false,
                'free_space' => 'N/A',
                'total_space' => 'N/A',
                'used_percentage' => 0,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    #[Computed]
    public function backupLogs()
    {
        $query = BackupLog::with('claim')
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply date filter
        $query = match ($this->dateFilter) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'year' => $query->whereYear('created_at', now()->year),
            default => $query,
        };

        // Apply search
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('source_path', 'like', "%{$this->searchQuery}%")
                    ->orWhere('backup_path', 'like', "%{$this->searchQuery}%")
                    ->orWhereHas('claim', function ($cq) {
                        $cq->where('nama_pasien', 'like', "%{$this->searchQuery}%")
                            ->orWhere('no_sep', 'like', "%{$this->searchQuery}%");
                    });
            });
        }

        return $query->paginate(15);
    }

    public function retryBackup(int $logId): void
    {
        $log = BackupLog::find($logId);

        if (! $log || $log->status !== 'failed') {
            return;
        }

        // Reset status to pending
        $log->update(['status' => 'pending', 'error_message' => null]);

        // Dispatch backup job
        \App\Jobs\BackupFileJob::dispatch(
            $log->source_path,
            null,
            $log->bpjs_claims_id
        );

        session()->flash('message', 'Backup retry telah dijadwalkan');
    }

    public function refreshDiskStatus(): void
    {
        unset($this->diskStatus);
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }

    public function render()
    {
        return view('livewire.backup-dashboard');
    }
}
