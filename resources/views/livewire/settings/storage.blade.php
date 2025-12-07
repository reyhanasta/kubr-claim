<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Pengaturan Penyimpanan')" :subheading="__('Kelola lokasi folder penyimpanan file klaim')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            {{-- Folder Shared --}}
            <div class="space-y-2">
                <flux:field>
                    <flux:label>{{ __('Folder Penyimpanan Utama') }}</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model="folder_shared" type="text" required
                            placeholder="Z:/FOLDER KLAIM REGULER BPJS" class="flex-1" />
                        <flux:button type="button" variant="filled" wire:click="openBrowser('shared')">
                            <flux:icon.folder-open class="size-4" />
                        </flux:button>
                    </div>
                </flux:field>

                {{-- Quick Presets for Shared --}}
                <div class="flex flex-wrap gap-2">
                    <flux:text size="xs" class="text-zinc-400 w-full">Quick select:</flux:text>
                    @foreach($availableDrives as $drive)
                        <flux:button size="xs" variant="ghost" wire:click="selectPreset('shared', '{{ $drive['path'] }}')"
                            type="button">
                            @if($drive['type'] === 'network')
                                <flux:icon.globe-alt class="size-3 mr-1" />
                            @else
                                <flux:icon.server class="size-3 mr-1" />
                            @endif
                            {{ $drive['label'] }}
                        </flux:button>
                    @endforeach
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if($sharedWritable)
                            <flux:badge color="green" size="sm">
                                <flux:icon.check-circle class="size-3 mr-1" />
                                Dapat diakses
                            </flux:badge>
                        @else
                            <flux:badge color="red" size="sm">
                                <flux:icon.x-circle class="size-3 mr-1" />
                                Tidak dapat diakses
                            </flux:badge>
                        @endif
                    </div>
                    <flux:button variant="primary" size="sm" color="green" wire:click="testConnection('shared')"
                        type="button">
                        Test Koneksi
                    </flux:button>
                </div>
            </div>

            {{-- Folder Backup --}}
            <div class="space-y-2">
                <flux:field>
                    <flux:label>{{ __('Folder Backup') }}</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model="folder_backup" type="text" required
                            placeholder="D:/Backup Folder Klaim BPJS" class="flex-1" />
                        <flux:button type="button" variant="filled" wire:click="openBrowser('backup')">
                            <flux:icon.folder-open class="size-4" />
                        </flux:button>
                    </div>
                </flux:field>

                {{-- Quick Presets for Backup --}}
                <div class="flex flex-wrap gap-2">
                    <flux:text size="xs" class="text-zinc-400 w-full">Quick select:</flux:text>
                    @foreach($availableDrives as $drive)
                        <flux:button size="xs" variant="ghost" wire:click="selectPreset('backup', '{{ $drive['path'] }}')"
                            type="button">
                            @if($drive['type'] === 'network')
                                <flux:icon.globe-alt class="size-3 mr-1" />
                            @else
                                <flux:icon.server class="size-3 mr-1" />
                            @endif
                            {{ $drive['label'] }}
                        </flux:button>
                    @endforeach
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if($backupWritable)
                            <flux:badge color="green" size="sm">
                                <flux:icon.check-circle class="size-3 mr-1" />
                                Dapat diakses
                            </flux:badge>
                        @else
                            <flux:badge color="red" size="sm">
                                <flux:icon.x-circle class="size-3 mr-1" />
                                Tidak dapat diakses
                            </flux:badge>
                        @endif
                    </div>
                    <flux:button icon="x-circle" variant="primary" size="sm" color="green"
                        wire:click="testConnection('backup')" type="button">
                        Test Koneksi
                    </flux:button>
                </div>
            </div>

            {{-- Auto Backup Toggle --}}
            <div class="flex items-center justify-between py-4 border-t border-zinc-200 dark:border-zinc-700">
                <div>
                    <flux:heading size="sm">Backup Otomatis</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        Aktifkan backup otomatis setiap kali file berhasil disimpan
                    </flux:text>
                </div>
                <flux:switch wire:model="auto_backup" />
            </div>

            {{-- Info Box --}}
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>Perhatian</flux:callout.heading>
                <flux:callout.text>
                    Mengubah path folder akan mempengaruhi lokasi penyimpanan file baru.
                    File yang sudah ada tidak akan dipindahkan secara otomatis.
                    Pastikan folder dapat diakses oleh server sebelum menyimpan.
                </flux:callout.text>
            </flux:callout>

            <div class="flex items-center gap-4 pt-4">
                <flux:button icon="check" variant="primary" type="submit" class="w-full sm:w-auto">
                    {{ __('Simpan Pengaturan') }}
                </flux:button>

                <x-action-message class="me-3" on="settings-saved">
                    {{ __('Tersimpan.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>

    {{-- Folder Browser Modal --}}
    <flux:modal wire:model="showBrowser" class="w-full max-w-2xl">
        <div class="space-y-4">
            <flux:heading size="lg">
                <flux:icon.folder class="size-5 mr-2 inline" />
                Pilih Folder {{ $browsingFor === 'shared' ? 'Penyimpanan Utama' : 'Backup' }}
            </flux:heading>

            {{-- Current Path --}}
            <div class="flex items-center gap-2 p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                <flux:button size="sm" variant="ghost" wire:click="navigateUp"
                    :disabled="$currentBrowsePath === dirname($currentBrowsePath)">
                    <flux:icon.arrow-up class="size-4" />
                </flux:button>
                <flux:text class="font-mono text-sm flex-1 truncate">{{ $currentBrowsePath }}</flux:text>
                <flux:button size="sm" variant="ghost" wire:click="loadFolders('{{ $currentBrowsePath }}')">
                    <flux:icon.arrow-path class="size-4" wire:loading.class="animate-spin" wire:target="loadFolders" />
                </flux:button>
            </div>

            {{-- Drive Selection --}}
            <div class="flex flex-wrap gap-2">
                @foreach($availableDrives as $drive)
                    <flux:button size="sm"
                        :variant="str_starts_with($currentBrowsePath, $drive['path']) ? 'primary' : 'ghost'"
                        wire:click="navigateTo('{{ $drive['path'] }}')" type="button">
                        @if($drive['type'] === 'network')
                            <flux:icon.globe-alt class="size-4 mr-1" />
                        @else
                            <flux:icon.server class="size-4 mr-1" />
                        @endif
                        {{ $drive['label'] }}
                    </flux:button>
                @endforeach
            </div>

            {{-- Folder List --}}
            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg max-h-64 overflow-y-auto">
                @forelse($currentFolders as $folder)
                    <div wire:click="navigateTo('{{ $folder['path'] }}')"
                        class="flex items-center gap-3 px-3 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                        <flux:icon.folder class="size-5 text-amber-500" />
                        <span class="flex-1 truncate">{{ $folder['name'] }}</span>
                        @if($folder['writable'])
                            <flux:badge color="green" size="sm">OK</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">Read-only</flux:badge>
                        @endif
                    </div>
                @empty
                    <div class="px-3 py-8 text-center text-zinc-500">
                        <flux:icon.folder-open class="size-8 mx-auto mb-2 opacity-50" />
                        <p>Tidak ada subfolder</p>
                    </div>
                @endforelse
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="ghost" wire:click="closeBrowser">
                    Batal
                </flux:button>
                <flux:button variant="primary" wire:click="selectFolder">
                    <flux:icon.check class="size-4 mr-1" />
                    Pilih Folder Ini
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>

@script
<script>
    $wire.on('show-alert', (data) => {
        const alertData = data[0];
        if (alertData.type === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: alertData.message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: alertData.message
            });
        }
    });
</script>
@endscript