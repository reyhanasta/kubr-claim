<?php

namespace App\Livewire\Concerns;

trait ManagesFileOrdering
{
    public function moveUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }

        $this->swapFiles($index, $index - 1);
    }

    public function moveDown(int $index): void
    {
        if ($index >= count($this->scanned_docs) - 1) {
            return;
        }

        $this->swapFiles($index, $index + 1);
    }

    protected function swapFiles(int $index1, int $index2): void
    {
        $arrays = ['scanned_docs', 'previewUrls'];

        foreach ($arrays as $arrayName) {
            if (isset($this->{$arrayName}[$index1]) && isset($this->{$arrayName}[$index2])) {
                [$this->{$arrayName}[$index1], $this->{$arrayName}[$index2]] =
                    [$this->{$arrayName}[$index2], $this->{$arrayName}[$index1]];
            }
        }
    }
}
