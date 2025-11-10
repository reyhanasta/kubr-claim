<?php

namespace App\Livewire\Dashboard;

use App\Models\BpjsClaim;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BpjsClaimDashboard extends Component
{
    public $month;

    public $year;

    public $summary = [];

    public $jenisRawatanChart = [];

    public $monthlyChart = [];

    public $jenisRawatanPerBulanChart = [];

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
        $this->refreshData();
    }

    public function updated($property): void
    {
        if (in_array($property, ['month', 'year'])) {
            $this->refreshData();
        }
    }

    public function refreshData(): void
    {
        $cacheKey = "dashboard_claims_{$this->year}_{$this->month}";

        // Cache for 5 minutes
        $data = Cache::remember($cacheKey, 300, function () {
            return [
                'summary' => $this->getSummaryData(),
                'monthly' => $this->getMonthlyData(),
                'jenis_rawatan' => $this->getJenisRawatanData(),
            ];
        });

        $this->summary = $data['summary'];
        $this->monthlyChart = $data['monthly'];
        $this->jenisRawatanPerBulanChart = $data['jenis_rawatan'];
    }

    protected function getSummaryData(): array
    {
        $query = BpjsClaim::query()
            ->when($this->month, fn ($q) => $q->whereMonth('tanggal_rawatan', $this->month))
            ->when($this->year, fn ($q) => $q->whereYear('tanggal_rawatan', $this->year));

        // Use single query for better performance
        $stats = DB::table('bpjs_claims')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN jenis_rawatan = "RI" THEN 1 ELSE 0 END) as total_ri,
                SUM(CASE WHEN jenis_rawatan = "RJ" THEN 1 ELSE 0 END) as total_rj,
                SUM(CASE WHEN kelas_rawatan = "Kelas 1" THEN 1 ELSE 0 END) as total_kelas1,
                SUM(CASE WHEN kelas_rawatan = "Kelas 2" THEN 1 ELSE 0 END) as total_kelas2,
                SUM(CASE WHEN kelas_rawatan = "Kelas 3" THEN 1 ELSE 0 END) as total_kelas3
            ')
            ->when($this->month, fn ($q) => $q->whereMonth('tanggal_rawatan', $this->month))
            ->when($this->year, fn ($q) => $q->whereYear('tanggal_rawatan', $this->year))
            ->first();

        return [
            'total_claims' => $stats->total ?? 0,
            'total_ri' => $stats->total_ri ?? 0,
            'total_rj' => $stats->total_rj ?? 0,
            'total_kelas1' => $stats->total_kelas1 ?? 0,
            'total_kelas2' => $stats->total_kelas2 ?? 0,
            'total_kelas3' => $stats->total_kelas3 ?? 0,
        ];
    }

    protected function getMonthlyData(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $monthExpr = "CAST(strftime('%m', tanggal_rawatan) AS INTEGER)";
        } else {
            $monthExpr = 'MONTH(tanggal_rawatan)';
        }

        $monthlyData = BpjsClaim::select(DB::raw("{$monthExpr} as month"), DB::raw('count(*) as total'))
            ->whereYear('tanggal_rawatan', $this->year)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = $monthlyData[$i] ?? 0;
        }

        return $result;
    }

    protected function getJenisRawatanData(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $monthExpr = "CAST(strftime('%m', tanggal_rawatan) AS INTEGER)";
        } else {
            $monthExpr = 'MONTH(tanggal_rawatan)';
        }

        $jenisRawatanData = BpjsClaim::select(
            DB::raw("{$monthExpr} as month"),
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

        return [
            'labels' => $labels,
            'rj' => $rjData,
            'ri' => $riData,
        ];
    }

    public function clearCache(): void
    {
        Cache::forget("dashboard_claims_{$this->year}_{$this->month}");
        $this->refreshData();

        session()->flash('message', 'Cache berhasil dibersihkan');
    }

    public function render()
    {
        return view('livewire.dashboard.bpjs-claim-dashboard');
    }
}
