<?php

namespace App\Livewire;

use App\Services\PdfReadService;
use Illuminate\Support\Env;
use Livewire\Component;
use Spatie\PdfToText\Pdf;
use Livewire\WithFileUploads;

class BpjsRawatJalan extends Component
{
   
    
    public function render()
    {
        return view('livewire.bpjs-rawat-jalan');
    }
}
