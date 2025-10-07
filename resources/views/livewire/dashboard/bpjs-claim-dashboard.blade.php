<div class="p-6 space-y-8">
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
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
        <div class="bg-gray-100 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700">Kelas 1</h3>
            <p class="text-2xl font-bold">{{ $summary['total_claims'] ?? 0 }}</p>
        </div>
        <div class="bg-green-100 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700">Kelas 2</h3>
            <p class="text-2xl font-bold">{{ $summary['total_ri'] ?? 0 }}</p>
        </div>
        <div class="bg-blue-100 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700">Kelas 3</h3>
            <p class="text-2xl font-bold">{{ $summary['total_rj'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-4 rounded-lg shadow">
            <h4 class="text-center font-semibold mb-2">Klaim per Jenis Rawatan</h4>
            <canvas id="jenisRawatanChart" height="100"></canvas>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <h4 class="text-center font-semibold mb-2">Klaim per Bulan ({{ $year }})</h4>
            <canvas id="monthlyChart" height="100"></canvas>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:navigated', initCharts);
        document.addEventListener('livewire:load', initCharts);

        function initCharts() {
            const monthlyData = @json($monthlyChart);
            const jenisRawatanData = @json($jenisRawatanPerBulanChart);

            // Destroy existing charts
            if (window.monthlyChartInstance) window.monthlyChartInstance.destroy();
            if (window.jenisRawatanChartInstance) window.jenisRawatanChartInstance.destroy();

            // --- Grafik Klaim per Bulan ---
            const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
            window.monthlyChartInstance = new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Jumlah Klaim',
                        data: monthlyData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });

            // --- Grafik Perbandingan RJ vs RI per Bulan ---
            const ctxJenis = document.getElementById('jenisRawatanChart').getContext('2d');
            window.jenisRawatanChartInstance = new Chart(ctxJenis, {
                type: 'bar',
                data: {
                    labels: jenisRawatanData.labels,
                    datasets: [
                        {
                            label: 'Rawat Jalan (RJ)',
                            data: jenisRawatanData.rj,
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        },
                        {
                            label: 'Rawat Inap (RI)',
                            data: jenisRawatanData.ri,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } },
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Perbandingan RJ & RI per Bulan' }
                    }
                }
            });
        }
    </script>

</div>