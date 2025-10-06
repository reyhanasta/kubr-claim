<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\BpjsClaim;
use Illuminate\Support\Facades\DB;

class BpjsClaimDashboard extends Component
{
    public $month;
    public $year;
    public $search = '';

    public $summary = [];
    public $jenisRawatanChart = [];
    public $monthlyChart = [];

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;

        $this->refreshData();
    }

    public function updated($property)
    {
        if (in_array($property, ['month', 'year'])) {
            $this->refreshData();
        }
    }
    

    public function refreshData()
    {
        $query = BpjsClaim::query()
            ->when($this->month, fn($q) => $q->whereMonth('tanggal_rawatan', $this->month))
            ->when($this->year, fn($q) => $q->whereYear('tanggal_rawatan', $this->year));

        // Hitung total klaim
        $riCount = (clone $query)->where('jenis_rawatan', 'RI')->count();
        $rjCount = (clone $query)->where('jenis_rawatan', 'RJ')->count();
        $totalCount = $riCount + $rjCount;

        $this->summary = [
            'total_claims' => $totalCount,
            'total_ri' => $riCount,
            'total_rj' => $rjCount,
        ];

        // Grafik Pie - per jenis rawatan
        $this->jenisRawatanChart = [
            'Rawat Inap (RI)' => $riCount,
            'Rawat Jalan (RJ)' => $rjCount,
        ];

        // Grafik Bar - per bulan (selama tahun berjalan)
        $monthlyData = BpjsClaim::select(DB::raw('MONTH(tanggal_rawatan) as month'), DB::raw('count(*) as total'))
            ->whereYear('tanggal_rawatan', $this->year)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $this->monthlyChart = [];
        for ($i = 1; $i <= 12; $i++) {
            $this->monthlyChart[] = $monthlyData[$i] ?? 0;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.bpjs-claim-dashboard');
    }
}
