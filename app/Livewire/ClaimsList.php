<?php

namespace App\Livewire;

use App\Models\BpjsClaim;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ClaimsList extends Component
{
    use WithPagination;

    public $search = '';

    public $filterJenisRawatan = '';

    public $filterKelas = '';

    public $filterMonth = '';

    public $filterYear = '';

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    public $perPage = 12;

    public $selectedClaims = [];

    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterJenisRawatan' => ['except' => ''],
        'filterKelas' => ['except' => ''],
    ];

    public function mount()
    {
        // Start without filters to show all claims
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterJenisRawatan()
    {
        $this->resetPage();
    }

    public function updatingFilterKelas()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterYear()
    {
        $this->resetPage();
    }

    #[Computed]
    public function claims()
    {
        return BpjsClaim::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_pasien', 'like', '%'.$this->search.'%')
                        ->orWhere('no_sep', 'like', '%'.$this->search.'%')
                        ->orWhere('no_rm', 'like', '%'.$this->search.'%')
                        ->orWhere('no_kartu_bpjs', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterJenisRawatan, fn ($query) => $query->where('jenis_rawatan', $this->filterJenisRawatan))
            ->when($this->filterKelas, fn ($query) => $query->where('kelas_rawatan', $this->filterKelas))
            ->when($this->filterMonth, fn ($query) => $query->whereMonth('tanggal_rawatan', $this->filterMonth))
            ->when($this->filterYear, fn ($query) => $query->whereYear('tanggal_rawatan', $this->filterYear))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function downloadFile($claimId)
    {
        $claim = BpjsClaim::findOrFail($claimId);
        $disk = Storage::disk('shared');

        if (! $disk->exists($claim->file_path)) {
            LivewireAlert::title('File tidak ditemukan')
                ->error()
                ->text('File tidak ada di storage')
                ->show();

            return;
        }

        // Redirect to download route
        return redirect()->route('claims.download', $claimId);
    }

    public function downloadLip($claimId)
    {
        $claim = BpjsClaim::findOrFail($claimId);

        if (! $claim->lip_file_path) {
            LivewireAlert::title('File LIP tidak ada')
                ->warning()
                ->text('Klaim ini tidak memiliki file LIP')
                ->show();

            return;
        }

        $disk = Storage::disk('shared');

        if (! $disk->exists($claim->lip_file_path)) {
            LivewireAlert::title('File tidak ditemukan')
                ->error()
                ->text('File LIP tidak ada di storage')
                ->show();

            return;
        }

        return redirect()->route('claims.download-lip', $claimId);
    }

    public function downloadMultiple()
    {
        if (empty($this->selectedClaims)) {
            LivewireAlert::toast()
                ->warning()
                ->title('Pilih klaim terlebih dahulu')
                ->position('top-end')
                ->timer(3000)
                ->show();

            return;
        }

        // Store selected IDs in session and redirect
        session(['download_claims' => $this->selectedClaims]);

        return redirect()->route('claims.download-multiple');
    }

    public function deleteClaim($claimId)
    {
        $claim = BpjsClaim::findOrFail($claimId);

        // Optional: Delete physical file
        // $disk = Storage::disk('shared');
        // if ($disk->exists($claim->file_path)) {
        //     $disk->delete($claim->file_path);
        // }

        $claim->delete();

        LivewireAlert::title('Berhasil')
            ->success()
            ->text('Klaim berhasil dihapus')
            ->show();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedClaims = $this->claims->pluck('id')->toArray();
        } else {
            $this->selectedClaims = [];
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterJenisRawatan', 'filterKelas', 'filterMonth', 'filterYear']);
    }

    public function render()
    {
        return view('livewire.claims-list');
    }
}
