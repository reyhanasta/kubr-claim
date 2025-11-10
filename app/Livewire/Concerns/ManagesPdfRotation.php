<?php

namespace App\Livewire\Concerns;

use CzProject\PdfRotate\PdfRotate;
use Illuminate\Support\Facades\Log;

trait ManagesPdfRotation
{
    public array $rotations = [];

    public function rotateFile(int $index): void
    {
        // Rotate by 90 degrees
        $this->rotations[$index] = (($this->rotations[$index] ?? 0) + 90) % 360;

        // Apply rotation to physical file if it exists
        if (isset($this->rotatedPaths[$index])) {
            $fullPath = storage_path('app/public/'.$this->rotatedPaths[$index]);

            if (file_exists($fullPath)) {
                $this->rotatePdf($fullPath, 90);

                Log::info('File rotated', [
                    'index' => $index,
                    'rotation' => $this->rotations[$index],
                ]);
            }
        }
    }

    protected function rotatePdf(string $filePath, int $rotation): bool
    {
        if (! in_array($rotation, [0, 90, 180, 270])) {
            Log::warning('Invalid rotation value', ['rotation' => $rotation]);

            return false;
        }

        if (! file_exists($filePath)) {
            Log::warning('File does not exist for rotation', ['path' => $filePath]);

            return false;
        }

        try {
            $rotator = new PdfRotate;
            $rotator->rotatePdf($filePath, $filePath, (int) $rotation);

            return true;
        } catch (\Exception $e) {
            Log::error('PDF rotation failed', [
                'error' => $e->getMessage(),
                'file' => $filePath,
            ]);

            return false;
        }
    }

    protected function resetRotations(): void
    {
        $this->rotations = [];
    }
}
