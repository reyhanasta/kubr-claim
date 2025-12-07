<?php

namespace App\Livewire\Settings;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class Storage extends Component
{
    public string $folder_shared = '';

    public string $folder_backup = '';

    public bool $auto_backup = true;

    public bool $sharedWritable = false;

    public bool $backupWritable = false;

    // For folder browser
    public array $availableDrives = [];

    public array $currentFolders = [];

    public string $browsingFor = ''; // 'shared' or 'backup'

    public string $currentBrowsePath = '';

    public bool $showBrowser = false;

    public function mount(): void
    {
        // Only admin can access
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $settings = AppSetting::getByGroup('storage');

        $this->folder_shared = $settings['folder_shared'] ?? '';
        $this->folder_backup = $settings['folder_backup'] ?? '';
        $this->auto_backup = $settings['auto_backup'] ?? true;

        $this->checkPaths();
        $this->scanAvailableDrives();
    }

    public function scanAvailableDrives(): void
    {
        $this->availableDrives = [];

        if (PHP_OS_FAMILY === 'Windows') {
            // Scan Windows drives A-Z
            foreach (range('C', 'Z') as $letter) {
                $drive = $letter.':';
                if (is_dir($drive.'/')) {
                    $this->availableDrives[] = [
                        'path' => $drive.'/',
                        'label' => $drive,
                        'type' => $this->getDriveType($drive),
                    ];
                }
            }
        } else {
            // Linux/Unix - scan common mount points
            $mountPoints = ['/mnt', '/media', '/home', '/var/www'];
            foreach ($mountPoints as $mount) {
                if (is_dir($mount)) {
                    $this->availableDrives[] = [
                        'path' => $mount,
                        'label' => $mount,
                        'type' => 'folder',
                    ];
                }
            }
        }
    }

    protected function getDriveType(string $drive): string
    {
        // Check if it's a network drive (Windows)
        if (PHP_OS_FAMILY === 'Windows') {
            $output = @shell_exec("net use {$drive} 2>&1");
            if ($output && str_contains($output, 'Microsoft Windows Network')) {
                return 'network';
            }
        }

        return 'local';
    }

    public function openBrowser(string $for): void
    {
        $this->browsingFor = $for;
        $this->currentBrowsePath = $for === 'shared' ? $this->folder_shared : $this->folder_backup;

        // If path is empty or invalid, start from first available drive
        if (empty($this->currentBrowsePath) || ! is_dir($this->currentBrowsePath)) {
            $this->currentBrowsePath = $this->availableDrives[0]['path'] ?? 'C:/';
        }

        $this->loadFolders($this->currentBrowsePath);
        $this->showBrowser = true;
    }

    public function closeBrowser(): void
    {
        $this->showBrowser = false;
        $this->currentFolders = [];
        $this->browsingFor = '';
    }

    public function loadFolders(string $path): void
    {
        $this->currentBrowsePath = rtrim(str_replace('\\', '/', $path), '/');
        $this->currentFolders = [];

        if (! is_dir($path)) {
            return;
        }

        try {
            $directories = File::directories($path);

            foreach ($directories as $dir) {
                $name = basename($dir);
                // Skip hidden and system folders
                if (str_starts_with($name, '.') || str_starts_with($name, '$')) {
                    continue;
                }

                $this->currentFolders[] = [
                    'name' => $name,
                    'path' => str_replace('\\', '/', $dir),
                    'writable' => is_writable($dir),
                ];
            }

            // Sort alphabetically
            usort($this->currentFolders, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        } catch (\Exception $e) {
            // Permission denied or other error
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Tidak dapat membaca folder: '.$e->getMessage(),
            ]);
        }
    }

    public function navigateTo(string $path): void
    {
        $this->loadFolders($path);
    }

    public function navigateUp(): void
    {
        $parent = dirname($this->currentBrowsePath);
        if ($parent !== $this->currentBrowsePath) {
            $this->loadFolders($parent);
        }
    }

    public function selectFolder(): void
    {
        if ($this->browsingFor === 'shared') {
            $this->folder_shared = $this->currentBrowsePath;
        } else {
            $this->folder_backup = $this->currentBrowsePath;
        }

        $this->checkPaths();
        $this->closeBrowser();
    }

    public function selectPreset(string $for, string $path): void
    {
        if ($for === 'shared') {
            $this->folder_shared = $path;
        } else {
            $this->folder_backup = $path;
        }
        $this->checkPaths();
    }

    public function checkPaths(): void
    {
        $this->sharedWritable = $this->isPathWritable($this->folder_shared);
        $this->backupWritable = $this->isPathWritable($this->folder_backup);
    }

    protected function isPathWritable(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Normalize path
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (! File::isDirectory($path)) {
            return false;
        }

        return is_writable($path);
    }

    public function testConnection(string $type): void
    {
        $path = $type === 'shared' ? $this->folder_shared : $this->folder_backup;
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (! File::isDirectory($path)) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => "Folder tidak ditemukan: {$path}",
            ]);

            return;
        }

        if (! is_writable($path)) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => "Folder tidak dapat ditulis: {$path}",
            ]);

            return;
        }

        // Try to create a test file
        $testFile = $path.DIRECTORY_SEPARATOR.'.fastclaim_test_'.time();
        try {
            File::put($testFile, 'test');
            File::delete($testFile);

            $this->dispatch('show-alert', [
                'type' => 'success',
                'message' => 'Koneksi berhasil! Folder dapat diakses.',
            ]);

            $this->checkPaths();
        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => "Gagal menulis ke folder: {$e->getMessage()}",
            ]);
        }
    }

    public function save(): void
    {
        $this->validate([
            'folder_shared' => ['required', 'string', 'max:500'],
            'folder_backup' => ['required', 'string', 'max:500'],
        ]);

        AppSetting::set('folder_shared', $this->folder_shared);
        AppSetting::set('folder_backup', $this->folder_backup);
        AppSetting::set('auto_backup', $this->auto_backup);

        // Update .env file values (for immediate use)
        $this->updateEnvFile();

        $this->checkPaths();
        $this->dispatch('settings-saved');
    }

    protected function updateEnvFile(): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return;
        }

        $envContent = File::get($envPath);

        // Update FOLDER_SHARED
        $envContent = preg_replace(
            '/^FOLDER_SHARED=.*/m',
            'FOLDER_SHARED="'.$this->folder_shared.'"',
            $envContent
        );

        // Update FOLDER_BACKUP
        $envContent = preg_replace(
            '/^FOLDER_BACKUP=.*/m',
            'FOLDER_BACKUP="'.$this->folder_backup.'"',
            $envContent
        );

        File::put($envPath, $envContent);

        // Clear config cache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    public function render()
    {
        return view('livewire.settings.storage');
    }
}
