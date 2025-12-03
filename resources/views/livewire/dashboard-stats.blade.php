<div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md border border-gray-100 dark:border-gray-800">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            Statistik Klaim BPJS
        </h2>
        <div class="flex gap-2">
            <select wire:model="selectedMonth"
                class="border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select wire:model="selectedYear"
                class="border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                @foreach (range(2023, now()->year) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
        <div
            class="p-4 bg-emerald-100 dark:bg-emerald-800/50 rounded-lg border border-emerald-200 dark:border-emerald-700">
            <h3 class="text-emerald-700 dark:text-emerald-300">Total Klaim</h3>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                {{ $totalClaims }}
            </p>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-800/30 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-gray-700 dark:text-gray-300 mb-2">Kelas Rawatan</h3>
            @forelse ($kelasStats as $kelas => $total)
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $kelas }}: <strong>{{ $total }}</strong>
                </p>
            @empty
                <p class="text-sm text-gray-400">Tidak ada data</p>
            @endforelse
        </div>

        <div class="p-4 bg-amber-50 dark:bg-amber-900/30 rounded-lg border border-amber-200 dark:border-amber-700">
            <h3 class="text-amber-700 dark:text-amber-300 mb-2">Jenis Rawatan</h3>
            @forelse ($jenisStats as $jenis => $total)
                <p class="text-sm text-amber-600 dark:text-amber-300">
                    {{ $jenis }}: <strong>{{ $total }}</strong>
                </p>
            @empty
                <p class="text-sm text-amber-400">Tidak ada data</p>
            @endforelse
        </div>
    </div>
</div>