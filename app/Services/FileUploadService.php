<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FileUploadService
{
    private const TEMP_STORAGE_PATH = 'temp';

    /**
     * Store file to temporary storage and return path info.
     */
    public function storeTemporary(TemporaryUploadedFile $file, string $key): array
    {
        $filename = $this->generateUniqueFilename($file);
        $storedPath = $file->storeAs(self::TEMP_STORAGE_PATH, $filename, 'public');

        Log::info("File {$key} uploaded", compact('filename', 'storedPath'));

        return [
            'path' => $storedPath,
            'filename' => $filename,
        ];
    }

    /**
     * Generate preview URL for stored file.
     */
    public function generatePreviewUrl(string $storedPath, bool $useDirectUrl = false): string
    {
        return $useDirectUrl
            ? url('storage/'.$storedPath)
            : Storage::url($storedPath);
    }

    /**
     * Generate unique filename with timestamp prefix.
     */
    public function generateUniqueFilename(TemporaryUploadedFile $file): string
    {
        return uniqid('', true).'_'.$file->getClientOriginalName();
    }

    /**
     * Cleanup temporary files by paths.
     */
    public function cleanupTempFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (str_starts_with($path, self::TEMP_STORAGE_PATH.'/') && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Validate file size.
     *
     * @throws \RuntimeException if file exceeds max size
     */
    public function validateFileSize(TemporaryUploadedFile $file, int $maxSizeKb, string $fileName = 'File'): void
    {
        if ($file->getSize() / 1024 > $maxSizeKb) {
            throw new \RuntimeException("{$fileName} maksimal {$maxSizeKb} KB");
        }
    }
}
