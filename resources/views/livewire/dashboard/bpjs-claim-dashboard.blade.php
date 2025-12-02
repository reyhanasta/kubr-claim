<div class="p-6 space-y-8 bg-white dark:bg-sage-900 dark:text-sage-100 transition-colors duration-300">
    {{-- Filter --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label class="text-sm font-semibold text-sage-700 dark:text-sage-300">Tahun</label>
            <input type="number" wire:model.live="year"
                class="w-full border border-sage-300 rounded p-1 dark:bg-sage-800 dark:border-sage-700 dark:text-sage-100 focus:ring-sage-500 focus:border-sage-500">
        </div>
        <div>
            <label class="text-sm font-semibold text-sage-700 dark:text-sage-300">Bulan</label>
            <select wire:model.live="month"
                class="w-full border border-sage-300 rounded p-1 dark:bg-sage-800 dark:border-sage-700 dark:text-sage-100 focus:ring-sage-500 focus:border-sage-500">
                <option value="">Semua</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
        <div class="bg-sage-100 dark:bg-sage-800 p-4 rounded-lg shadow border border-sage-200 dark:border-sage-700">
            <h3 class="font-semibold text-sage-700 dark:text-sage-300">Total Klaim</h3>
            <p class="text-2xl font-bold text-sage-900 dark:text-sage-100">{{ $summary['total_claims'] ?? 0 }}</p>
        </div>
        <div class="bg-sage-50 dark:bg-sage-800/50 p-4 rounded-lg shadow border border-sage-200 dark:border-sage-700">
            <h3 class="font-semibold text-sage-700 dark:text-sage-300">Rawat Inap (RI)</h3>
            <p class="text-2xl font-bold text-sage-900 dark:text-sage-100">{{ $summary['total_ri'] ?? 0 }}</p>
        </div>
        <div class="bg-sage-100 dark:bg-sage-800 p-4 rounded-lg shadow border border-sage-200 dark:border-sage-700">
            <h3 class="font-semibold text-sage-700 dark:text-sage-300">Rawat Jalan (RJ)</h3>
            <p class="text-2xl font-bold text-sage-900 dark:text-sage-100">{{ $summary['total_rj'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
        <div class="bg-sage-50 dark:bg-sage-800/50 p-4 rounded-lg shadow border border-sage-200 dark:border-sage-700">
            <h3 class="font-semibold text-sage-700 dark:text-sage-300">Kelas 1</h3>
            <p class="text-2xl font-bold text-sage-900 dark:text-sage-100">{{ $summary['total_kelas1'] ?? 0 }}</p>
        </div>
        <div class="bg-sage-100 dark:bg-sage-800 p-4 rounded-lg shadow border border-sage-200 dark:border-sage-700">
            <h3 class="font-semibold text-sage-700 dark:text-sage-300">Kelas 2</h3>
            <p class="text-2xl font-bold text-sage-900 dark:text-sage-100">{{ $summary['total_kelas2'] ?? 0 }}</p>
        </div>
        <div class="bg-sage-50 dark:bg-sage-800/50 p-4 rounded-lg shadow border border-sage-200 dark:border-sage-700">
            <h3 class="font-semibold text-sage-700 dark:text-sage-300">Kelas 3</h3>
            <p class="text-2xl font-bold text-sage-900 dark:text-sage-100">{{ $summary['total_kelas3'] ?? 0 }}</p>
        </div>
    </div>

    <hr class="border-sage-300 dark:border-sage-700">

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white dark:bg-sage-800 p-4 rounded-lg shadow border border-sage-200 dark:border-sage-700">
            <h4 class="text-center font-semibold mb-2 text-sage-800 dark:text-sage-200">Klaim per Jenis Rawatan</h4>
            <canvas id="jenisRawatanChart" height="120"></canvas>
        </div>

        <div class="bg-white dark:bg-sage-800 p-4 rounded-lg shadow border border-sage-200 dark:border-sage-700">
            <h4 class="text-center font-semibold mb-2 text-sage-800 dark:text-sage-200">Klaim per Bulan ({{ $year }})
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

            const textColor = document.documentElement.classList.contains('dark') ? '#D2DCB6' : '#5A6B56';
            const gridColor = document.documentElement.classList.contains('dark') ? '#4A5944' : '#D2DCB6';

            // --- Grafik Klaim per Bulan ---
            const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
            window.monthlyChartInstance = new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Jumlah Klaim',
                        data: monthlyData,
                        backgroundColor: 'rgba(119, 136, 115, 0.7)',
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
                        { label: 'Rawat Jalan (RJ)', data: jenisRawatanData.rj, backgroundColor: 'rgba(161, 188, 152, 0.8)' },
                        { label: 'Rawat Inap (RI)', data: jenisRawatanData.ri, backgroundColor: 'rgba(90, 107, 86, 0.8)' }
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