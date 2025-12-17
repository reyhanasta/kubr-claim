<div class="min-h-screen bg-gray-50 dark:bg-gray-900">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Daftar Klaim BPJS
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Kelola dan cari klaim yang telah dibuat
                    </p>
                </div>
                <flux:button icon="plus" variant="primary" href="{{ route('bpjs-rajal-form') }}" wire:navigate>
                    Buat Klaim Baru
                </flux:button>
            </div>
        </div>
        {{-- Search & Filters --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="lg:col-span-2">
                    <flux:input wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, No. SEP, No. RM, No. Kartu..." icon="magnifying-glass" />
                </div>

                {{-- Filter Jenis Rawatan --}}
                <div>
                    <flux:select wire:model.live="filterJenisRawatan">
                        <option value="">Semua Jenis</option>
                        <option value="RJ">Rawat Jalan</option>
                        <option value="RI">Rawat Inap</option>
                    </flux:select>
                </div>

                {{-- Filter Kelas --}}
                <div>
                    <flux:select wire:model.live="filterKelas">
                        <option value="">Semua Kelas</option>
                        <option value="Kelas 1">Kelas 1</option>
                        <option value="Kelas 2">Kelas 2</option>
                        <option value="Kelas 3">Kelas 3</option>
                    </flux:select>
                </div>
            </div>

            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    {{-- Month Filter --}}
                    <flux:select wire:model.live="filterMonth" class="w-40">
                        <option value="">Semua Bulan</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}">
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </flux:select>

                    {{-- Year Filter --}}
                    <flux:select wire:model.live="filterYear" class="w-32">
                        <option value="">Semua Tahun</option>
                        @foreach(range(2020, now()->year) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </flux:select>
                </div>


            </div>
        </div>

        {{-- Bulk Actions --}}
        {{-- @if($this->claims->isNotEmpty())
        <div class="mb-4 flex items-center gap-2">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" wire:model.live="selectAll"
                    class="rounded border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700">
                Pilih semua di halaman ini
            </label>
        </div>
        @endif --}}

        {{-- Cards Grid --}}
        @if($this->claims->isEmpty())
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <flux:icon name="document-magnifying-glass" class="size-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak ada klaim ditemukan</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    @if($search)
                        Tidak ada hasil untuk pencarian "{{ $search }}"
                    @else
                        Belum ada klaim yang dibuat pada periode ini
                    @endif
                </p>
                <flux:button variant="primary" href="{{ route('bpjs-rajal-form') }}" wire:navigate>
                    Buat Klaim Pertama
                </flux:button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mb-6">
                @foreach($this->claims as $claim)
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
                        {{-- Card Header --}}
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        {{-- <input type="checkbox" wire:model.live="selectedClaims" value="{{ $claim->id }}"
                                            class="rounded border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700">
                                        --}}
                                        <flux:badge variant="{{ $claim->jenis_rawatan === 'RJ' ? 'info' : 'warning' }}">
                                            {{ $claim->jenis_rawatan === 'RJ' ? 'R. Jalan' : 'R. Inap' }}
                                        </flux:badge>
                                        <flux:badge variant="ghost">
                                            {{ $claim->kelas_rawatan }}
                                        </flux:badge>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $claim->nama_pasien }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        No. SEP: <span
                                            class="font-mono text-gray-700 dark:text-gray-300">{{ $claim->no_sep }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-6 space-y-3">
                            <div class="flex items-center text-sm">
                                <flux:icon name="calendar" class="size-4 text-gray-400 dark:text-gray-500 mr-2" />
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ $claim->tanggal_rawatan->translatedFormat('d F Y') }}
                                </span>
                            </div>
                            <div class="flex items-center text-sm">
                                <flux:icon name="identification" class="size-4 text-gray-400 dark:text-gray-500 mr-2" />
                                <span class="text-gray-600 dark:text-gray-400">
                                    No. RM: {{ $claim->no_rm }}
                                </span>
                            </div>
                            <div class="flex items-center text-sm">
                                <flux:icon name="credit-card" class="size-4 text-gray-400 dark:text-gray-500 mr-2" />
                                <span class="text-gray-600 dark:text-gray-400 font-mono text-xs">
                                    {{ $claim->no_kartu_bpjs }}
                                </span>
                            </div>
                            <div class="flex items-center text-sm">
                                <flux:icon name="clock" class="size-4 text-gray-400 dark:text-gray-500 mr-2" />
                                <span class="text-gray-500 dark:text-gray-400 text-xs">
                                    Dibuat {{ $claim->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div
                            class="p-2  rounded-b-lg bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex  gap-1">
                            <flux:button icon="arrow-down-tray" variant="primary" wire:click="downloadFile({{ $claim->id }})"
                                class="flex-1">
                                Unduh
                                PDF
                            </flux:button>
                            @if($claim->lip_file_path)
                                <flux:button icon="document-text" variant="primary" color="orange"
                                    wire:click="downloadLip({{ $claim->id }})" class="flex-1">
                                    Unduh
                                    LIP
                                </flux:button>
                            @endif
                            <flux:button variant="danger" icon="trash" class="flex-1"
                                x-on:click="if(confirm('Apakah Anda yakin ingin menghapus klaim ini?')) $wire.deleteClaim({{ $claim->id }})">

                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                {{ $this->claims->links() }}
            </div>
        @endif
    </div>
</div>