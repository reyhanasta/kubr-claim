<div>
    <div wire:offline>
        This device is currently offline.
    </div>

    <div>
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
            <div class="max-w-4xl mx-auto p-2 rounded-xl shadow-lg">
                <!-- PDF Preview SEP -->
                <div class="w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-lg relative"
                    style="padding-top: 34%">
                    <iframe src="{{ $previewUrls['sepFile'] ?? '' }}#zoom=120&toolbar=0&navpanes=0&scrollbar=0"
                        class="absolute top-0 left-0 w-full h-full" frameborder="0">
                    </iframe>
                </div>
            </div>

            <div wire:submit.prevent="submit" wire:target="submit" class="space-y-4">
                <!-- Patient Info Section -->
                <div
                    class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl shadow-lg">
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

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                        <flux:input type="text" icon="document-text" wire:model="sep_number" placeholder="Nomor SEP"
                            label="Nomor SEP" />
                        <flux:input type="date" wire:model="sep_date" placeholder="Tanggal SEP" label="Tanggal SEP" />
                        <flux:input type="text" wire:model="patient_class" placeholder="Kelas Pasien"
                            label="Kelas Pasien" />
                    </div>
                </div>

                <!-- Upload File Resume & Billing -->
                <div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg">
                    <h1 class="text-2xl font-bold mb-4">Input Dokumen Penunjang</h1>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-4">
                        <!-- Resume -->
                        <flux:field>
                            <flux:label>
                                <flux:icon.document-text variant="solid" class="text-pink-600 mr-1.5" />
                                File Resume Medis
                            </flux:label>
                            <flux:input type="file" wire:model="resumeFile" accept=".pdf"
                                placeholder="Unggah File Resume Medis" />
                        </flux:field>

                        <!-- Billing -->
                        <flux:field>
                            <flux:label>
                                <flux:icon.document-currency-dollar variant="solid" class="text-amber-600 mr-1.5" />
                                File Billing
                            </flux:label>
                            <flux:input type="file" wire:model="billingFile" accept=".pdf,.jpg,.png"
                                placeholder="Unggah File Billing" />
                        </flux:field>

                        <!-- File LIP -->
                        <flux:field>
                            <flux:label>
                                <flux:icon.document-plus variant="solid"
                                    class="text-purple-600 dark:text-purple-400 mr-1.5" />
                                Dokumen LIP
                            </flux:label>
                            <flux:description>File LIP (tidak ikut di-merge, disimpan terpisah)</flux:description>
                            <flux:input type="file" wire:model="fileLIP" accept=".pdf" placeholder="Unggah File LIP" />
                        </flux:field>

                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end mt-10 gap-4">
                        <flux:button wire:click="cancelForm" variant="filled" icon="x-circle"
                            class="w-1/5 px-12 font-medium">
                            Batal
                        </flux:button>
                        <flux:button wire:click="submit" variant="primary" icon="arrow-down-tray"
                            class="w-1/5 px-12 bg-emerald-600 text-white font-medium rounded-lg">
                            Simpan Dokumen
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Jika SEP belum di-upload --}}
        @if(!$showUploadedData)
            <div class="max-w-4xl mx-auto p-10 bg-zinc-50 dark:bg-zinc-900 rounded-xl shadow-lg">
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

    <!-- Loading Overlay -->
    <div wire:loading.flex wire:target="sepFile,resumeFile,billingFile"
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