<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    {{-- Statistik Cards --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">


        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Tahun Ini</h2>
                <p class="mt-2 text-3xl font-bold text-blue-600">{{ $filesThisYear }}</p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total SEP tahun {{ now()->year }}</div>
        </div>
        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Bulan Ini</h2>
                <p class="mt-2 text-3xl font-bold text-purple-600">{{ $filesThisMonth }}</p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">SEP diinput pada bulan {{ now()->format('F') }}
            </div>
        </div>
        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Indikasi File Duplikat</h2>
                <p class="mt-2 text-3xl font-bold {{ $duplicateFilesCount <= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $duplicateFilesCount }}
                </p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">File tahun {{ now()->year }}</div>
        </div>
    </div>

    {{-- Tabel Daftar File --}}
    <div
        class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Daftar File</h2>


            {{-- Search Input --}}

            <div class="mb-4 sm:flex sm:items-center sm:justify-between">
                <flux:input icon="magnifying-glass" placeholder="Search orders" wire:model.live.debounce.300ms="search"
                    placeholder="Cari berdasarkan nama atau NIK..." clearable />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Nama
                            Pasien
                        </th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Path</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Tanggal
                            Upload</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Tanggal
                            SEP</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Aksi</th>


                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($files as $file)

                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $file->patient_name }}</td>

                            <td class="px-4 py-2 text-sm text-blue-600 dark:text-blue-400">{{ $file->no_sep }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ $file->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ date('d-m-Y', strtotime($file->tanggal_rawatan)) }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">

                                <flux:button variant="primary" href="{{ route('bpjs-rajal-form-edit', $file->id) }}"
                                    color="amber">
                                    Edit
                                </flux:button>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500">Tidak ada file ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $files->links() }}
        </div>
    </div>
</div>