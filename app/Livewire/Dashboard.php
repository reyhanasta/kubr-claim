<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BpjsClaim;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $search = '';

    protected $updatesQueryString = ['search']; // biar pencarian masuk ke URL
    protected $paginationTheme = 'tailwind'; // biar pagination style pakai Tailwind

    public function updatingSearch()
    {
        $this->resetPage(); // reset ke halaman 1 kalau ada perubahan search
    }

    public function render()
    {
        $query = BpjsClaim::query();

        if ($this->search) {
            $query->where('patient_name', 'like', "%{$this->search}%")
                  ->orWhere('no_sep', 'like', "%{$this->search}%");
        }

        $files = $query->latest()->paginate(10); // pagination 10 data per halaman

        return view('livewire.dashboard', [
            'files' => $files,
            'totalFiles' => BpjsClaim::count(),
            'filesThisYear' => BpjsClaim::whereYear('created_at', now()->year)->count(),
            'filesThisMonth' => BpjsClaim::whereYear('created_at', now()->year)
                                        ->whereMonth('created_at', now()->month)
                                        ->count(),
            'duplicateFilesCount' => BpjsClaim::select('no_sep')
                                        ->groupBy('no_sep')
                                        ->havingRaw('COUNT(*) > 1')
                                        ->count(),
        ]);
    }
}
