<div class="p-6 space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <flux:heading size="xl" class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <flux:icon.server-stack class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                </div>
                Backup Dashboard
            </flux:heading>
            <flux:subheading class="mt-1">
                Monitor status backup dan kesehatan storage
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:click="refreshDiskStatus" icon="arrow-path" variant="ghost">
                Refresh
            </flux:button>
        </div>
    </div>

    {{-- Disk Status Card --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <flux:heading size="sm" class="flex items-center gap-2">
                <flux:icon.server class="w-5 h-5 text-gray-500" />
                Status Disk Backup
            </flux:heading>
        </div>
        <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                {{-- Status Indicator --}}
                <div class="flex items-center gap-3">
                    @if($this->diskStatus['accessible'])
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full">
                            <flux:icon.check-circle class="w-8 h-8 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <flux:text class="font-semibold text-emerald-700 dark:text-emerald-300">Healthy</flux:text>
                            <flux:text size="sm" class="text-gray-500">Disk dapat diakses</flux:text>
                        </div>
                    @else
                        <div class="p-3 bg-rose-100 dark:bg-rose-900/30 rounded-full">
                            <flux:icon.exclamation-triangle class="w-8 h-8 text-rose-600 dark:text-rose-400" />
                        </div>
                        <div>
                            <flux:text class="font-semibold text-rose-700 dark:text-rose-300">Error</flux:text>
                            <flux:text size="sm" class="text-gray-500">Disk tidak dapat diakses</flux:text>
                        </div>
                    @endif
                </div>

                {{-- Disk Info --}}
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <flux:text size="xs" class="text-gray-500 uppercase tracking-wide">Path</flux:text>
                        <flux:text size="sm" class="font-mono truncate">{{ $this->diskStatus['path'] ?? 'N/A' }}
                        </flux:text>
                    </div>
                    <div>
                        <flux:text size="xs" class="text-gray-500 uppercase tracking-wide">Free Space</flux:text>
                        <flux:text size="sm" class="font-semibold">{{ $this->diskStatus['free_space'] }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="xs" class="text-gray-500 uppercase tracking-wide">Total Space</flux:text>
                        <flux:text size="sm" class="font-semibold">{{ $this->diskStatus['total_space'] }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="xs" class="text-gray-500 uppercase tracking-wide">Used</flux:text>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $this->diskStatus['used_percentage'] > 90 ? 'bg-rose-500' : ($this->diskStatus['used_percentage'] > 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                    style="width: {{ $this->diskStatus['used_percentage'] }}%"></div>
                            </div>
                            <flux:text size="sm" class="font-semibold">{{ $this->diskStatus['used_percentage'] }}%
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {{-- Total Backups --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <flux:icon.archive-box class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:text size="2xl" class="font-bold">{{ $this->stats['total'] }}</flux:text>
                    <flux:text size="xs" class="text-gray-500">Total</flux:text>
                </div>
            </div>
        </div>

        {{-- Success --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <flux:icon.check-circle class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <flux:text size="2xl" class="font-bold text-emerald-600 dark:text-emerald-400">
                        {{ $this->stats['success'] }}</flux:text>
                    <flux:text size="xs" class="text-gray-500">Sukses</flux:text>
                </div>
            </div>
        </div>

        {{-- Failed --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-rose-100 dark:bg-rose-900/30 rounded-lg">
                    <flux:icon.x-circle class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                </div>
                <div>
                    <flux:text size="2xl" class="font-bold text-rose-600 dark:text-rose-400">
                        {{ $this->stats['failed'] }}</flux:text>
                    <flux:text size="xs" class="text-gray-500">Gagal</flux:text>
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                    <flux:icon.clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:text size="2xl" class="font-bold text-amber-600 dark:text-amber-400">
                        {{ $this->stats['pending'] }}</flux:text>
                    <flux:text size="xs" class="text-gray-500">Pending</flux:text>
                </div>
            </div>
        </div>

        {{-- Success Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-violet-100 dark:bg-violet-900/30 rounded-lg">
                    <flux:icon.chart-pie class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <flux:text size="2xl" class="font-bold">{{ $this->stats['success_rate'] }}%</flux:text>
                    <flux:text size="xs" class="text-gray-500">Success Rate</flux:text>
                </div>
            </div>
        </div>

        {{-- Total Size --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg">
                    <flux:icon.circle-stack class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                </div>
                <div>
                    <flux:text size="lg" class="font-bold">{{ $this->stats['total_size'] }}</flux:text>
                    <flux:text size="xs" class="text-gray-500">Total Size</flux:text>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col md:flex-row gap-4">
            {{-- Search --}}
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="searchQuery"
                    placeholder="Cari nama pasien, no SEP, atau path..." icon="magnifying-glass" />
            </div>

            {{-- Status Filter --}}
            <div class="w-full md:w-40">
                <flux:select wire:model.live="statusFilter">
                    <option value="all">Semua Status</option>
                    <option value="success">Sukses</option>
                    <option value="failed">Gagal</option>
                    <option value="pending">Pending</option>
                </flux:select>
            </div>

            {{-- Date Filter --}}
            <div class="w-full md:w-40">
                <flux:select wire:model.live="dateFilter">
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                    <option value="all">Semua</option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Flash Message --}}
    @if (session('message'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4">
            <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300">
                <flux:icon.check-circle class="w-5 h-5" />
                {{ session('message') }}
            </div>
        </div>
    @endif

    {{-- Backup Logs Table --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <flux:heading size="sm" class="flex items-center gap-2">
                <flux:icon.clipboard-document-list class="w-5 h-5 text-gray-500" />
                Log Backup
            </flux:heading>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Waktu</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Klaim</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Tipe</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Size</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Path Backup</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->backupLogs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td class="px-4 py-3">
                                <div class="text-gray-900 dark:text-gray-100">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($log->claim)
                                    <div class="text-gray-900 dark:text-gray-100">{{ $log->claim->nama_pasien }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $log->claim->no_sep }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $log->file_type === 'merged' ? 'blue' : 'violet' }}">
                                    {{ strtoupper($log->file_type) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-400">
                                {{ $log->formatted_file_size }}
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $log->status_badge_color }}">
                                    {{ ucfirst($log->status) }}
                                </flux:badge>
                                @if($log->status === 'failed' && $log->error_message)
                                    <div class="text-xs text-rose-600 dark:text-rose-400 mt-1 max-w-xs truncate"
                                        title="{{ $log->error_message }}">
                                        {{ Str::limit($log->error_message, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-mono text-gray-500 max-w-xs truncate"
                                    title="{{ $log->backup_path }}">
                                    {{ $log->backup_path ?? '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($log->status === 'failed')
                                    <flux:button wire:click="retryBackup({{ $log->id }})" size="xs" variant="ghost"
                                        icon="arrow-path" wire:loading.attr="disabled">
                                        Retry
                                    </flux:button>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon.inbox class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                                    <flux:text class="text-gray-500">Tidak ada data backup</flux:text>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($this->backupLogs->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $this->backupLogs->links() }}
            </div>
        @endif
    </div>
</div>