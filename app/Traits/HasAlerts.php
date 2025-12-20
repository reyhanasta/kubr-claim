<?php

declare(strict_types=1);

namespace App\Traits;

use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

trait HasAlerts
{
    /**
     * Show success alert.
     */
    protected function showSuccessAlert(string $title, string $text): void
    {
        LivewireAlert::title($title)
            ->success()
            ->text($text)
            ->show();
    }

    /**
     * Show error alert.
     */
    protected function showErrorAlert(string $title, string $text): void
    {
        LivewireAlert::title($title)
            ->error()
            ->text($text)
            ->timer(5000)
            ->show();
    }

    /**
     * Show warning alert as toast.
     */
    protected function showWarningAlert(string $title, string $text): void
    {
        LivewireAlert::toast()
            ->warning()
            ->title($title)
            ->text($text)
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    /**
     * Show info alert as toast.
     */
    protected function showInfoAlert(string $title, string $text): void
    {
        LivewireAlert::toast()
            ->info()
            ->title($title)
            ->text($text)
            ->position('top-end')
            ->timer(3000)
            ->show();
    }
}
