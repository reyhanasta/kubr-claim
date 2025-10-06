<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\BpjsClaim;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BpjsClaimDashboard extends Component
{
    public $month;
    public $year;
    public $jenisRawatan = '';
    public $kelasRawatan = '';

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function getSummaryProperty()
    {
        $query = BpjsClaim::query()
            ->when($this->month, fn($q) => $q->whereMonth('tanggal_rawatan', $this->month))
            ->when($this->year, fn($q) => $q->whereYear('tanggal_rawatan', $this->year))
            ->when($this->jenisRawatan, fn($q) => $q->where('jenis_rawatan', $this->jenisRawatan))
            ->when($this->kelasRawatan, fn($q) => $q->where('kelas_rawatan', $this->kelasRawatan));

        return [
            'total' => $query->count(),
            'rawat_jalan' => (clone $query)->where('jenis_rawatan', 'Rawat Jalan')->count(),
            'rawat_inap' => (clone $query)->where('jenis_rawatan', 'Rawat Inap')->count(),
            'igd' => (clone $query)->where('jenis_rawatan', 'IGD')->count(),
        ];
    }

    public function getPerKelasProperty()
    {
        return BpjsClaim::select('kelas_rawatan', DB::raw('count(*) as total'))
            ->when($this->month, fn($q) => $q->whereMonth('tanggal_rawatan', $this->month))
            ->when($this->year, fn($q) => $q->whereYear('tanggal_rawatan', $this->year))
            ->groupBy('kelas_rawatan')
            ->pluck('total', 'kelas_rawatan');
    }

    public function getMonthlyChartProperty()
    {
        return BpjsClaim::select(DB::raw('MONTH(tanggal_rawatan) as month'), DB::raw('count(*) as total'))
            ->whereYear('tanggal_rawatan', $this->year)
            ->groupBy('month')
            ->pluck('total', 'month');
    }

    public function render()
    {
        return view('livewire.dashboard.bpjs-claim-dashboard', [
            'summary' => $this->summary,
            'kelasStats' => $this->perKelas,
            'monthlyChart' => $this->monthlyChart,
            'claims' => BpjsClaim::query()
                ->when($this->month, fn($q) => $q->whereMonth('tanggal_rawatan', $this->month))
                ->when($this->year, fn($q) => $q->whereYear('tanggal_rawatan', $this->year))
                ->when($this->jenisRawatan, fn($q) => $q->where('jenis_rawatan', $this->jenisRawatan))
                ->when($this->kelasRawatan, fn($q) => $q->where('kelas_rawatan', $this->kelasRawatan))
                ->latest('tanggal_rawatan')
                ->take(10)
                ->get(),
        ]);
    }
}
