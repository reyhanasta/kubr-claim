<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ManagesFileUploads
{
    public array $scanned_docs = [];

    public array $previewUrls = [];

    public array $rotatedPaths = [];

    public bool $showPreviewModal = false;

    public ?int $currentPreviewIndex = null;

    public function getCurrentPreviewUrlProperty(): string
    {
        if ($this->currentPreviewIndex !== null && isset($this->previewUrls[$this->currentPreviewIndex])) {
            return $this->previewUrls[$this->currentPreviewIndex];
        }

        return '';
    }

    public function previewFile(int $index): void
    {
        if (isset($this->scanned_docs[$index])) {
            $this->currentPreviewIndex = $index;
            $this->showPreviewModal = true;
        }
    }

    public function closePreviewModal(): void
    {
        $this->showPreviewModal = false;
        $this->currentPreviewIndex = null;
    }

    public function removeFile(int $index): void
    {
        try {
            if (! isset($this->scanned_docs[$index])) {
                Log::error('File not found in scanned_docs', ['index' => $index]);

                return;
            }

            // Delete rotated file if exists
            if (isset($this->rotatedPaths[$index]) && Storage::disk('public')->exists($this->rotatedPaths[$index])) {
                Storage::disk('public')->delete($this->rotatedPaths[$index]);
            }

            // Remove the file from arrays
            unset($this->scanned_docs[$index]);
            unset($this->previewUrls[$index]);
            unset($this->rotatedPaths[$index]);

            // Reindex arrays to maintain proper order
            $this->scanned_docs = array_values($this->scanned_docs);
            $this->previewUrls = array_values($this->previewUrls);
            $this->rotatedPaths = array_values($this->rotatedPaths);

            Log::info('File removed successfully', ['index' => $index]);
        } catch (\Exception $e) {
            Log::error('Error removing file', [
                'error' => $e->getMessage(),
                'index' => $index,
            ]);
        }
    }

    public function clearAllFiles(): void
    {
        foreach ($this->rotatedPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $this->resetFileProperties();
    }

    protected function resetFileProperties(): void
    {
        $this->scanned_docs = [];
        $this->previewUrls = [];
        $this->rotatedPaths = [];
        $this->showPreviewModal = false;
        $this->currentPreviewIndex = null;
    }

    protected function storeTemporaryFile($file): array
    {
        $filename = Str::uuid()->toString().'_'.$file->getClientOriginalName();
        $storedPath = $file->storeAs('temp', $filename, 'public');
        $previewUrl = Storage::url($storedPath);

        Log::info('File stored temporarily', [
            'filename' => $filename,
            'path' => $storedPath,
        ]);

        return [
            'path' => $storedPath,
            'url' => $previewUrl,
        ];
    }
}
