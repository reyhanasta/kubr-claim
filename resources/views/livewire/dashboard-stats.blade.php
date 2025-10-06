<div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            Statistik Klaim BPJS
        </h2>
        <div class="flex gap-2">
            <select wire:model="selectedMonth" class="border-gray-300 rounded-lg">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select wire:model="selectedYear" class="border-gray-300 rounded-lg">
                @foreach (range(2023, now()->year) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
        <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
            <h3 class="text-gray-700 dark:text-gray-300">Total Klaim</h3>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-300">
                {{ $totalClaims }}
            </p>
        </div>

        <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
            <h3 class="text-gray-700 dark:text-gray-300 mb-2">Kelas Rawatan</h3>
            @forelse ($kelasStats as $kelas => $total)
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $kelas }}: <strong>{{ $total }}</strong>
                </p>
            @empty
                <p class="text-sm text-gray-400">Tidak ada data</p>
            @endforelse
        </div>

        <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
            <h3 class="text-gray-700 dark:text-gray-300 mb-2">Jenis Rawatan</h3>
            @forelse ($jenisStats as $jenis => $total)
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $jenis }}: <strong>{{ $total }}</strong>
                </p>
            @empty
                <p class="text-sm text-gray-400">Tidak ada data</p>
            @endforelse
        </div>
    </div>
</div>