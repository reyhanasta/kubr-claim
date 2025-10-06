<div x-data="{
        jenisRawatanData: @entangle('jenisRawatanChart').live,
        monthlyData: @entangle('monthlyChart').live,
        jenisChart: null,
        monthlyChart: null,

        init() {
            const jenisCtx = document.getElementById('jenisRawatanChart').getContext('2d');
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

            // Chart jenis rawatan (Bar)
            this.jenisChart = new Chart(jenisCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(this.jenisRawatanData ?? {}),
                   datasets: [{
                        label: 'Jumlah Klaim',
                        data: this.monthlyData ?? [],
                        backgroundColor: '#3F51B5',
                    }]
                },
                ptions: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            // Chart klaim per bulan (Bar)
            this.monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                    datasets: [{
                        label: 'Jumlah Klaim',
                        data: this.monthlyData ?? [],
                        backgroundColor: '#3F51B5',
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            // Watch perubahan Livewire
            this.$watch('jenisRawatanData', val => this.updateJenis(val));
            this.$watch('monthlyData', val => this.updateMonthly(val));
        },

        updateJenis(val) {
            this.jenisChart.data.labels = Object.keys(val ?? {});
            this.jenisChart.data.datasets[0].data = Object.values(val ?? {});
            this.jenisChart.update();
        },
        updateMonthly(val) {
            this.monthlyChart.data.datasets[0].data = val ?? [];
            this.monthlyChart.update();
        }
    }" class="p-6 space-y-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Filter --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
            <label class="text-sm font-semibold">Tahun</label>
            <input type="number" wire:model.live="year" class="w-full border rounded p-1">
        </div>
        <div>
            <label class="text-sm font-semibold">Bulan</label>
            <select wire:model.live="month" class="w-full border rounded p-1">
                <option value="">Semua</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                @endfor
            </select>
        </div>
    </div>


    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
        <div class="bg-gray-100 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700">Total Klaim</h3>
            <p class="text-2xl font-bold">{{ $summary['total_claims'] ?? 0 }}</p>
        </div>
        <div class="bg-green-100 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700">Rawat Inap (RI)</h3>
            <p class="text-2xl font-bold">{{ $summary['total_ri'] ?? 0 }}</p>
        </div>
        <div class="bg-blue-100 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700">Rawat Jalan (RJ)</h3>
            <p class="text-2xl font-bold">{{ $summary['total_rj'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-4 rounded-lg shadow">
            <h4 class="text-center font-semibold mb-2">Klaim per Jenis Rawatan</h4>
            <canvas id="jenisRawatanChart" height="10"></canvas>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <h4 class="text-center font-semibold mb-2">Klaim per Bulan ({{ $year }})</h4>
            <canvas id="monthlyChart" height="100"></canvas>
        </div>
    </div>
</div>