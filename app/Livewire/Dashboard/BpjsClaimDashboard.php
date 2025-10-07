<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\BpjsClaim;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BpjsClaimDashboard extends Component
{
    public $month;
    public $year;

    public $summary = [];
    public $jenisRawatanChart = [];
    public $monthlyChart = [];
    public $jenisRawatanPerBulanChart = [];

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

        // 🔹 Grafik Bar - Klaim per bulan
        $monthlyData = BpjsClaim::select(DB::raw('MONTH(tanggal_rawatan) as month'), DB::raw('count(*) as total'))
            ->whereYear('tanggal_rawatan', $this->year)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $this->monthlyChart = [];
        for ($i = 1; $i <= 12; $i++) {
            $this->monthlyChart[] = $monthlyData[$i] ?? 0;
        }

        // 🔹 Grafik Bar 2: RJ & RI per bulan
        $jenisRawatanData = BpjsClaim::select(
                DB::raw('MONTH(tanggal_rawatan) as month'),
                DB::raw("SUM(CASE WHEN jenis_rawatan = 'RJ' THEN 1 ELSE 0 END) as total_rj"),
                DB::raw("SUM(CASE WHEN jenis_rawatan = 'RI' THEN 1 ELSE 0 END) as total_ri")
            )
            ->whereYear('tanggal_rawatan', $this->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $rjData = [];
        $riData = [];

        foreach ($jenisRawatanData as $row) {
            $labels[] = Carbon::create()->month($row->month)->translatedFormat('F');
            $rjData[] = (int) $row->total_rj;
            $riData[] = (int) $row->total_ri;
        }

        $this->jenisRawatanPerBulanChart = [
            'labels' => $labels,
            'rj' => $rjData,
            'ri' => $riData,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.bpjs-claim-dashboard');
    }
}
