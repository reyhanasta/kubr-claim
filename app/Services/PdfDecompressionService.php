<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PdfDecompressionService
{
    /**
     * Decompress PDF using Ghostscript to make it compatible with FPDI.
     */
    public function decompress(string $sourcePath): string
    {
        $tempPath = storage_path('app/temp_decompress_'.uniqid().'.pdf');

        $gsPath = $this->findGhostscript();

        if (! $gsPath) {
            throw new \Exception('Ghostscript tidak ditemukan. Silakan install dari https://ghostscript.com/releases/gsdnld.html');
        }

        // Ghostscript command to decompress PDF
        $command = sprintf(
            '"%s" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile="%s" "%s"',
            $gsPath,
            $tempPath,
            $sourcePath
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || ! file_exists($tempPath)) {
            throw new \Exception('Gagal dekompresi PDF dengan Ghostscript');
        }

        Log::info('PDF berhasil di-dekompresi', [
            'source' => basename($sourcePath),
            'output' => basename($tempPath),
        ]);

        return $tempPath;
    }

    /**
     * Find Ghostscript executable path.
     */
    protected function findGhostscript(): ?string
    {
        $possiblePaths = [
            'C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe',
            'C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe',
            'C:\\Program Files\\gs\\gs10.03.0\\bin\\gswin64c.exe',
            'C:\\Program Files\\gs\\gs10.02.1\\bin\\gswin64c.exe',
            'C:\\Program Files\\gs\\gs10.02.0\\bin\\gswin64c.exe',
            'C:\\Program Files\\gs\\gs10.01.2\\bin\\gswin64c.exe',
            'C:\\Program Files (x86)\\gs\\gs10.04.0\\bin\\gswin32c.exe',
            'C:\\Program Files (x86)\\gs\\gs10.03.1\\bin\\gswin32c.exe',
            'gs', // If in PATH
        ];

        foreach ($possiblePaths as $path) {
            if ($path === 'gs') {
                exec('where gs 2>nul', $output, $returnCode);
                if ($returnCode === 0 && ! empty($output)) {
                    return 'gs';
                }
            } elseif (file_exists($path)) {
                return $path;
            }
        }

        // Try to find dynamically
        $gsDir = 'C:\\Program Files\\gs';
        if (is_dir($gsDir)) {
            $versions = glob($gsDir.'\\gs*');
            if (! empty($versions)) {
                $latestVersion = end($versions);
                $gsExe = $latestVersion.'\\bin\\gswin64c.exe';
                if (file_exists($gsExe)) {
                    return $gsExe;
                }
            }
        }

        return null;
    }

    /**
     * Check if Ghostscript is available.
     */
    public function isAvailable(): bool
    {
        return $this->findGhostscript() !== null;
    }
}
