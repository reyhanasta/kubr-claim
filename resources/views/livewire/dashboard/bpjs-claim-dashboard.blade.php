<div class="p-6 space-y-8 bg-gray-50 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    {{-- Filter --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                </path>
            </svg>
            Filter Data
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">Tahun</label>
                <input type="number" wire:model.live="year"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">Bulan</label>
                <select wire:model.live="month"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Semua</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    {{-- Summary Cards - Row 1: Total, RI, RJ --}}
    <div wire:loading.remove wire:target="year,month">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            {{-- Total Klaim - Emerald Primary --}}
            <div
                class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-emerald-600 dark:from-emerald-600 dark:to-emerald-700 p-6 rounded-2xl shadow-lg text-white">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm font-medium">Total Klaim</p>
                            <p class="text-4xl font-bold mt-1">{{ $summary['total_claims'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white/20 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rawat Inap - Sky/Blue --}}
            <div
                class="relative overflow-hidden bg-gradient-to-br from-sky-500 to-blue-500 dark:from-sky-600 dark:to-blue-600 p-6 rounded-2xl shadow-lg text-white">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sky-100 text-sm font-medium">Rawat Inap (RI)</p>
                            <p class="text-4xl font-bold mt-1">{{ $summary['total_ri'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white/20 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rawat Jalan - Amber/Orange --}}
            <div
                class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-500 dark:from-amber-600 dark:to-orange-600 p-6 rounded-2xl shadow-lg text-white">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-amber-100 text-sm font-medium">Rawat Jalan (RJ)</p>
                            <p class="text-4xl font-bold mt-1">{{ $summary['total_rj'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white/20 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Skeleton for Stats --}}
    <div wire:loading wire:target="year,month">
        <x-skeleton.stats count="3" />
    </div>

    {{-- Summary Cards - Row 2: Kelas 1, 2, 3 --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        {{-- Kelas 1 - Emerald Accent --}}
        <div
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg border-l-4 border-emerald-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Kelas 1</p>
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $summary['total_kelas1'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Kelas 2 - Teal Accent --}}
        <div
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg border-l-4 border-teal-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Kelas 2</p>
                    <p class="text-3xl font-bold text-teal-600 dark:text-teal-400 mt-1">
                        {{ $summary['total_kelas2'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-teal-100 dark:bg-teal-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Kelas 3 - Green Accent --}}
        <div
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg border-l-4 border-green-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Kelas 3</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">
                        {{ $summary['total_kelas3'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
            <h4 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                Klaim per Jenis Rawatan
            </h4>
            <canvas id="jenisRawatanChart" height="140"></canvas>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
            <h4 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                Klaim per Bulan ({{ $year }})
            </h4>
            <canvas id="monthlyChart" height="140"></canvas>
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

            const isDark = document.documentElement.classList.contains('dark');
            // Standard gray colors for text and grid
            const textColor = isDark ? '#9ca3af' : '#4b5563'; // gray-400 / gray-600
            const gridColor = isDark ? '#374151' : '#e5e7eb'; // gray-700 / gray-200

            // --- Grafik Klaim per Bulan - Gradient Emerald ---
            const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
            const monthlyGradient = ctxMonthly.createLinearGradient(0, 0, 0, 300);
            monthlyGradient.addColorStop(0, 'rgba(16, 185, 129, 0.9)');  // emerald-500
            monthlyGradient.addColorStop(1, 'rgba(52, 211, 153, 0.5)'); // emerald-400

            window.monthlyChartInstance = new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Jumlah Klaim',
                        data: monthlyData,
                        backgroundColor: monthlyGradient,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: textColor }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
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
                            backgroundColor: 'rgba(245, 158, 11, 0.8)', // amber-500
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: 'Rawat Inap (RI)',
                            data: jenisRawatanData.ri,
                            backgroundColor: 'rgba(14, 165, 233, 0.8)', // sky-500
                            borderRadius: 6,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: textColor }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    }
                }
            });
        }
    </script>
</div>