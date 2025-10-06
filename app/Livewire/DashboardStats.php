<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BpjsClaim;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardStats extends Component
{
    public $selectedMonth;
    public $selectedYear;

    public $kelasStats = [];
    public $jenisStats = [];
    public $totalClaims = 0;

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;

        $this->loadStats();
    }

    public function updated($property)
    {
        if (in_array($property, ['selectedMonth', 'selectedYear'])) {
            $this->loadStats();
        }
    }

    public function loadStats()
    {
        $month = $this->selectedMonth;
        $year = $this->selectedYear;

        $cacheKey = "dashboard_stats_{$month}_{$year}";

        $data = Cache::remember($cacheKey, 600, function () use ($month, $year) {
            $kelasStats = BpjsClaim::select('kelas_rawat', DB::raw('count(*) as total'))
                ->whereMonth('sep_date', $month)
                ->whereYear('sep_date', $year)
                ->groupBy('kelas_rawat')
                ->pluck('total', 'kelas_rawat')
                ->toArray();

            $jenisStats = BpjsClaim::select('jenis_rawatan', DB::raw('count(*) as total'))
                ->whereMonth('sep_date', $month)
                ->whereYear('sep_date', $year)
                ->groupBy('jenis_rawatan')
                ->pluck('total', 'jenis_rawatan')
                ->toArray();

            $totalClaims = BpjsClaim::whereMonth('sep_date', $month)
                ->whereYear('sep_date', $year)
                ->count();

            return [
                'kelasStats' => $kelasStats,
                'jenisStats' => $jenisStats,
                'totalClaims' => $totalClaims,
            ];
        });

        $this->kelasStats = $data['kelasStats'];
        $this->jenisStats = $data['jenisStats'];
        $this->totalClaims = $data['totalClaims'];
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
