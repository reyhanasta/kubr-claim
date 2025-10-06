<div class="">
    <div wire:offline>
        This device is currently offline.
    </div>
    <div class="">
        <!-- Header -->
        <div class="mb-6 text-center">
            <flux:heading size="xl" level="1" class="text-blue-600 dark:text-amber-300">
                BPJS Claim Submission
            </flux:heading>
            <flux:subheading size="md" class="text-gray-600 dark:text-gray-300">
                Silakan isi detail pasien berikut.
            </flux:subheading>
        </div>

        {{-- Jika sudah upload SEP --}}
        @if($showUploadedData)
            <form wire:submit.prevent="submit" wire:target="submit" class="space-y-4">
                <!-- Patient Info Section -->
                <div
                    class="max-w-5xl mx-auto p-6 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-b-xl shadow-lg">
                    <h1 class="text-2xl font-bold">Data Pasien</h1>
                    <hr class="my-4 border-gray-300 dark:border-gray-600">

                    <!-- Data Pasien -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                        <!-- Nomor RM -->
                        <div class="group bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-l-4 border-blue-500">
                            <label class="block text-xs font-semibold text-blue-600 dark:text-blue-300 uppercase mb-1">
                                Nomor RM
                            </label>
                            <div class="font-mono text-lg font-medium">{{ $medical_record_number }}</div>
                        </div>

                        <!-- Nama Pasien -->
                        <div
                            class="group md:col-span-2 bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-l-4 border-orange-500">
                            <label class="block text-xs font-semibold text-orange-600 dark:text-orange-300 uppercase mb-1">
                                Nama Pasien
                            </label>
                            <div class="text-lg font-medium">{{ $patient_name }}</div>
                        </div>

                        <!-- Nomor Kartu BPJS -->
                        <div class="group bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-l-4 border-teal-500">
                            <label class="block text-xs font-semibold text-teal-600 dark:text-teal-300 uppercase mb-1">
                                Nomor Kartu BPJS
                            </label>
                            <div class="font-mono text-lg font-medium">{{ $bpjs_serial_number }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-2">
                        <flux:input type="text" icon="document-text" wire:model="sep_number" placeholder="Nomor SEP"
                            label="Nomor SEP" />
                        <flux:input type="date" wire:model="sep_date" placeholder="Tanggal SEP" label="Tanggal SEP" />
                        <flux:input type="text" wire:model="patient_class" placeholder="Kelas Pasien"
                            label="Kelas Pasien" />
                        <flux:input type="text" wire:model="jenis_rawatan" placeholder="Jenis Rawatan"
                            label="Jenis Rawatan" />
                    </div>
                </div>
                <div
                    class="max-w-5xl mx-auto p-6 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-b-xl shadow-lg">
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
                                            <iframe src="{{ $previewUrls[$index] }}#toolbar=0&navpanes=0&scrollbar=0"
                                                class="w-full h-full" frameborder="0">
                                            </iframe>
                                        </div>
                                    </div>

                                    <!-- File Name -->
                                    <div class="flex-1 truncate text-sm text-gray-100">
                                        {{ $doc->getClientOriginalName() }}
                                    </div>

                                    <!-- Actions -->
                                    {{-- <div class="flex gap-2">
                                        @if($index > 0)
                                        <flux:button icon="arrow-up" size="xs" variant="ghost"
                                            wire:click.prevent="moveUp({{ $index }})" title="Pindah ke atas" />
                                        @endif

                                        @if(
                                        $index
                                        < count($scanned_docs) - 1
                                        )
                                        <flux:button icon="arrow-down" size="xs" variant="ghost"
                                            wire:click.prevent="moveDown({{ $index }})" title="Pindah ke bawah" />
                                        @endif

                                        <flux:button icon="arrow-uturn-right" size="xs" variant="ghost"
                                            wire:click.prevent="rotateFile({{ $index }})" title="Putar PDF">

                                        </flux:button>

                                        <flux:button icon="eye" size="xs" variant="ghost"
                                            wire:click.prevent="previewFile({{ $index }})" title="Preview" />


                                        <flux:button icon="trash" size="xs" variant="subtle" class="text-red-400"
                                            wire:click.prevent="removeFile({{ $index }})" title="Hapus file" />
                                    </div> --}}
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

                </div>
                <!-- Upload File Resume & Billing -->
                <div class="flex justify-end mt-10 gap-4">
                    <flux:button wire:click="cancelForm" variant="filled" icon="x-circle" class="w-1/5 px-12 font-medium">
                        Batal
                    </flux:button>
                    <flux:button wire:click="submit" variant="primary" icon="arrow-down-tray"
                        class="w-1/5 px-12 bg-emerald-600 text-white font-medium rounded-lg">
                        Simpan Dokumen
                    </flux:button>
                </div>
            </form>
        @endif

        @if(!$showUploadedData)
            <div class="max-w-5xl mx-auto p-10 bg-zinc-50 dark:bg-zinc-900 rounded-xl shadow-lg">
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-medium mb-4">
                        <i class="fas fa-file-upload mr-2 text-blue-600"></i> Unggah Dokumen SEP (PDF)
                    </h3>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <label for="sepFile" class="cursor-pointer text-blue-600 hover:text-blue-500">
                                <span>Unggah file SEP</span>
                                <input id="sepFile" wire:model="sepFile" type="file" class="sr-only" accept=".pdf">
                            </label>
                            <p class="text-xs text-gray-500">PDF maksimal 2MB</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- Loading Overlays -->


    <!-- Loading Overlay -->
    <div wire:loading.flex wire:target="scanned_docs,new_docs,submit,"
        class="fixed inset-0 z-50 bg-neutral-900/60 flex items-center justify-center backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 text-center">
            <svg class="h-12 w-12 animate-spin text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-amber-600 font-semibold text-lg">Memproses file PDF, mohon tunggu...</p>
        </div>
    </div>

</div>