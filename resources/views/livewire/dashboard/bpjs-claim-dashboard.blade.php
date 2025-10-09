<div class="p-6 space-y-8 bg-white dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    {{-- Filter --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label class="text-sm font-semibold">Tahun</label>
            <input type="number" wire:model.live="year"
                class="w-full border rounded p-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
        </div>
        <div>
            <label class="text-sm font-semibold">Bulan</label>
            <select wire:model.live="month"
                class="w-full border rounded p-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                <option value="">Semua</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">Total Klaim</h3>
            <p class="text-2xl font-bold">{{ $summary['total_claims'] ?? 0 }}</p>
        </div>
        <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">Rawat Inap (RI)</h3>
            <p class="text-2xl font-bold">{{ $summary['total_ri'] ?? 0 }}</p>
        </div>
        <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">Rawat Jalan (RJ)</h3>
            <p class="text-2xl font-bold">{{ $summary['total_rj'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">Kelas 1</h3>
            <p class="text-2xl font-bold">{{ $summary['total_kelas1'] ?? 0 }}</p>
        </div>
        <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">Kelas 2</h3>
            <p class="text-2xl font-bold">{{ $summary['total_kelas2'] ?? 0 }}</p>
        </div>
        <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">Kelas 3</h3>
            <p class="text-2xl font-bold">{{ $summary['total_kelas3'] ?? 0 }}</p>
        </div>
    </div>

    <hr class="border-gray-300 dark:border-gray-700">

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h4 class="text-center font-semibold mb-2 text-gray-800 dark:text-gray-200">Klaim per Jenis Rawatan</h4>
            <canvas id="jenisRawatanChart" height="120"></canvas>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h4 class="text-center font-semibold mb-2 text-gray-800 dark:text-gray-200">Klaim per Bulan ({{ $year }})
            </h4>
            <canvas id="monthlyChart" height="120"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', initCharts);
        document.addEventListener('livewire:load', initCharts);

        function initCharts() {
            const monthlyData = @json($monthlyChart);
            const jenisRawatanData = @json($jenisRawatanPerBulanChart);

            if (window.monthlyChartInstance) window.monthlyChartInstance.destroy();
            if (window.jenisRawatanChartInstance) window.jenisRawatanChartInstance.destroy();

            const textColor = document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#1F2937';
            const gridColor = document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB';

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
                    scales: {
                        y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor } },
                        x: { grid: { color: gridColor }, ticks: { color: textColor } }
                    },
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
                        { label: 'Rawat Jalan (RJ)', data: jenisRawatanData.rj, backgroundColor: 'rgba(75, 192, 192, 0.7)' },
                        { label: 'Rawat Inap (RI)', data: jenisRawatanData.ri, backgroundColor: 'rgba(255, 159, 64, 0.7)' }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor } },
                        x: { grid: { color: gridColor }, ticks: { color: textColor } }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { color: textColor } },
                        title: { display: true, text: 'Perbandingan RJ & RI per Bulan', color: textColor }
                    }
                }
            });
        }
    </script>
</div>