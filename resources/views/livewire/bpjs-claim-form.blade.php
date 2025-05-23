<div class="max-w-4xl mx-auto p-8 bg-gray-700 text-gray-100 rounded-xl shadow-lg">
    <!-- Header -->
    <div class="mb-8 text-center">
        <flux:heading size="xl" level="1" class="text-amber">BPJS Claim Submission</flux:heading>
        <flux:subheading size="md" class="text-gray-200">Please fill in the patient's details below.</flux:subheading>
    </div>

    <form wire:submit.prevent="submit" wire:loading.attr="disabled" wire:target="submit" class="space-y-8">
        <!-- Patient Info Section -->
        <div class="grid grid-cols-4 gap-6">
            <!-- Nomor RM -->
            <div class="col-span-1">
                <flux:input name="no_rm" label="Nomor RM" type="text" wire:model.lazy="no_rm"
                    icon:trailing="{{ $rmIcon }}" placeholder="Nomor RM" wire:change="searchPatient"
                    badge="Wajib diisi" />
            </div>
            <!-- Nama Pasien -->
            <div class="col-span-2">
                <flux:input type="text" variant="filled" icon="user" wire:model.debounce.500ms="patient_name" readonly
                    class="cursor-not-allowed mt-1.5" label="Nama Pasien" placeholder="Terisi Otomatis" />
            </div>
            <div class="col-span-1">
                <flux:input type="text" variant="filled" icon="credit-card" wire:model.debounce.500ms="no_kartu_bpjs"
                    readonly class="cursor-not-allowed mt-1.5" label="Nomor Kartu BPJS" placeholder="Terisi Otomatis"
                    copyable />
            </div>
        </div>

        <!-- Additional Info Section -->
        <div class="grid grid-cols-4 gap-6">
            <!-- Nomor SEP -->
            <div class="col-span-2">
                <flux:input type="text" icon="document-text" wire:model.debounce.500ms="no_sep" placeholder="Nomor SEP"
                    label="Nomor SEP" badge="Wajib diisi" />
            </div>

            <!-- Jenis Rawatan -->
            <div>
                <flux:select wire:model="jenis_rawatan" label="Jenis Rawatan" placeholder="Pilih Jenis Rawatan"
                    badge="Wajib diisi">
                    <flux:select.option value="RAWAT JALAN">Rawat Jalan</flux:select.option>
                    <flux:select.option value="RAWAT INAP">Rawat Inap</flux:select.option>
                </flux:select>
            </div>

            <!-- Tanggal Dokumen -->
            <div>
                <flux:input type="date" wire:model="tanggal_rawatan" placeholder="Tanggal Rawatan"
                    label="Tanggal Rawatan" badge="Wajib diisi" />
            </div>
        </div>

        <!-- File Upload Section -->
        @if(empty($scanned_docs))
        <!-- Initial Upload -->
        <div>
            <flux:input type="file" label="Upload Scanned Documents" wire:model="scanned_docs" multiple
                accept=".pdf,.jpg,.png" placeholder="Upload Scanned Documents" />
        </div>
        @endif

        <!-- File Management Controls -->
        @if(!empty($scanned_docs))
        <div class="flex justify-end space-x-4 mb-4">
            <flux:input type="file" id="add-more-files" wire:model="new_docs" multiple accept=".pdf,.jpg,.png"
                class="hidden" placeholder="Tambahkan PDF" />
            <flux:button icon="plus" variant="subtle" class="text-amber-300 hover:text-amber-400"
                onclick="document.getElementById('add-more-files').click()">
                Tambahkan File
            </flux:button>
            @if(count($scanned_docs) > 1)
            <flux:button icon="trash" variant="subtle" wire:click="clearAllFiles"
                class="text-red-400 hover:text-red-600">
                Hapus Semua File
            </flux:button>
            @endif
        </div>

        <!-- Document List -->
        <div class="mt-4 space-y-2">
            @foreach($scanned_docs as $index => $doc)
            <div class="flex items-center gap-4 p-4 bg-gray-700 border border-gray-600 rounded-lg">
                <!-- Inline PDF Preview with rotation support -->
                <div
                    class="w-64 h-80 overflow-hidden border border-gray-500 rounded bg-white flex-shrink-0 shadow-sm relative">
                    <div class="w-full h-full"
                        style="transform: rotate({{ $rotations[$index] ?? 0 }}deg); transform-origin: center center; transition: transform 0.3s ease;">
                        <iframe src="{{ $previewUrls[$index] }}#toolbar=0&navpanes=0&scrollbar=0" class="w-full h-full"
                            frameborder="0">
                        </iframe>
                    </div>
                </div>

                <!-- File Name -->
                <div class="flex-1 truncate text-sm text-gray-100">
                    {{ $doc->getClientOriginalName() }}
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    @if($index > 0)
                    <flux:button icon="arrow-up" size="xs" variant="ghost" wire:click.prevent="moveUp({{ $index }})"
                        title="Pindah ke atas" />
                    @endif

                    @if($index
                    < count($scanned_docs) - 1) <flux:button icon="arrow-down" size="xs" variant="ghost"
                        wire:click.prevent="moveDown({{ $index }})" title="Pindah ke bawah" />
                    @endif

                    <flux:button icon="arrow-uturn-right" size="xs" variant="ghost"
                        wire:click.prevent="rotateFile({{ $index }})" title="Putar PDF">

                    </flux:button>

                    <flux:button icon="eye" size="xs" variant="ghost" wire:click.prevent="previewFile({{ $index }})"
                        title="Preview" />
                    {{--
                    <flux:button icon="eye" size="xs" variant="ghost"
                        wire:click.prevent="readPdfwithSpatie({{ $index }})" title="Baca PDf" /> --}}

                    <flux:button icon="trash" size="xs" variant="subtle" class="text-red-400"
                        wire:click.prevent="removeFile({{ $index }})" title="Hapus file" />
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Preview Modal with Rotation Support -->
        @if($showPreviewModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 p-4 overflow-auto"
            wire:click.self="$set('showPreviewModal', false)">
            <div class="relative bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="flex-shrink-0 flex justify-between items-center px-6 py-4 border-b border-gray-600">
                    <flux:heading size="md" level="3" class="text-xl font-semibold text-amber-300">
                        PDF Preview {{ $currentPreviewIndex !== null ? '- ' . ($rotations[$currentPreviewIndex] ?? 0) .
                        '°' : '' }}
                    </flux:heading>
                    <div class="flex items-center gap-4">
                        @if($currentPreviewIndex !== null)
                        <flux:button icon="arrow-uturn-right" variant="ghost"
                            wire:click="rotateFile({{ $currentPreviewIndex }})" title="Putar PDF">
                            Putar
                        </flux:button>
                        @endif
                        <flux:button icon="x-mark" variant="subtle" wire:click="closePreviewModal" />
                    </div>
                </div>

                <!-- Modal Content with Rotation -->
                <div class="flex-1 min-h-0 relative">
                    @if($currentPreviewIndex !== null && isset($previewUrls[$currentPreviewIndex]))
                    <div class="w-full h-full"
                        style="transform: rotate({{ $rotations[$currentPreviewIndex] ?? 0 }}deg); transform-origin: center center; transition: transform 0.3s ease;">
                        <iframe src="{{ $previewUrls[$currentPreviewIndex] }}" class="w-full h-full border-0"
                            frameborder="0" style="min-height: 80vh;">
                        </iframe>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Submit Button -->
        <div class="flex justify-between items-center">
            <div wire:dirty class="text-amber-300 italic">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Perubahan belum tersimpan...
            </div>
            <div>
                <flux:button variant="primary" type="submit" icon="arrow-down-tray"
                    class="active:scale-110 px-6 py-3 bg-emerald-600 text-white font-semibold rounded-lg transition-all duration-300 ease-in-out shadow-md hover:bg-emerald-500 hover:shadow-emerald-500/30 focus:outline-none focus:ring-4 focus:ring-emerald-500">
                    Simpan
                </flux:button>
            </div>

        </div>
    </form>

    <!-- Loading Overlays -->
    <div wire:loading wire:target="scanned_docs"
        class="fixed inset-0 z-50 bg-neutral-900/60 flex items-center justify-center backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 text-center animate-fade-in">
            <svg class="h-12 w-12 animate-spin text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-amber-100 font-semibold text-lg">Memproses file PDF, mohon tunggu...</p>
        </div>
    </div>

    <div wire:loading wire:target="new_docs"
        class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 text-center">
            <svg class="h-10 w-10 animate-spin text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-amber-100 font-medium text-md">Menambahkan file tambahan...</p>
        </div>
    </div>

    <div wire:loading wire:target="submit"
        class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 text-center">
            <svg class="h-12 w-12 animate-spin text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-emerald-100 font-semibold text-lg">Menyimpan klaim BPJS...</p>
        </div>
    </div>
</div>