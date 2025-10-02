<div class="">
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

        <div class="" wire:show='showUploadedData' wire:transition.scale.origin.top.duration.300ms>
            <div class="max-w-4xl mx-auto p-2 rounded-xl shadow-lg">
                <!-- PDF Preview -->
                <div class="w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-lg relative"
                    style="padding-top: 34%">
                    <iframe src="{{ $previewUrls[0] ?? '' }}#zoom=120&toolbar=0&navpanes=0&scrollbar=0"
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

                    <!-- SIMRS Data Display -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                        <!-- Nomor RM -->
                        <div
                            class="group bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-l-4 border-blue-500 hover:bg-gray-100 dark:hover:bg-gray-750 transition-all duration-200">
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path d="M9 4.804A7.968..." />
                                    </svg>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-blue-600 dark:text-blue-300 uppercase tracking-wider mb-1">Nomor
                                        RM</label>
                                    <div class="font-mono text-lg font-medium">{{ $simrs_rm_number }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Nama Pasien -->
                        <div
                            class="group md:col-span-2 bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-l-4 border-orange-500 hover:bg-gray-100 dark:hover:bg-gray-750 transition-all duration-200">
                            <div class="flex items-start gap-3">
                                <div class="bg-orange-100 dark:bg-orange-900/30 p-2 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-orange-600 dark:text-orange-400" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path d="M10 9a3..." />
                                    </svg>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-orange-600 dark:text-orange-300 uppercase tracking-wider mb-1">Nama
                                        Pasien SIMRS</label>
                                    <div class="text-lg font-medium">{{ $patient_name }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Nomor Kartu BPJS -->
                        <div
                            class="group bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-l-4 border-teal-500 hover:bg-gray-100 dark:hover:bg-gray-750 transition-all duration-200">
                            <div class="flex items-start gap-3">
                                <div class="bg-teal-100 dark:bg-teal-900/30 p-2 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-teal-600 dark:text-teal-400" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path d="M3 5a2..." />
                                    </svg>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-teal-600 dark:text-teal-300 uppercase tracking-wider mb-1">Nomor
                                        Kartu</label>
                                    <div class="font-mono text-lg font-medium">{{ $bpjs_serial_number }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEP Input -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" wire:show='patientValidated'>
                        <flux:input name="medical_record_number" label="Nomor RM SEP" type="text"
                            wire:model="medical_record_number" icon:trailing="{{ $rmIcon }}" placeholder="Nomor RM" />
                        <flux:input type="text" variant="filled" icon="user" wire:model.debounce.500ms="patient_name"
                            readonly class="md:col-span-2 cursor-not-allowed" label="Nama Pasien SEP"
                            placeholder="Terisi Otomatis" />
                        <flux:input type="text" variant="filled" icon="credit-card"
                            wire:model.debounce.500ms="bpjs_serial_number" readonly class="cursor-not-allowed"
                            label="Nomor Kartu BPJS SEP" placeholder="Terisi Otomatis" copyable />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                        <flux:input type="text" icon="document-text" wire:model.debounce.500ms="sep_number"
                            placeholder="Nomor SEP" label="Nomor SEP" description="Nomor SEP untuk klaim" />
                        <flux:input type="date" wire:model="sep_date" placeholder="Tanggal SEP" label="Tanggal SEP"
                            description="Tanggal digunakan untuk klaim" />
                        <flux:input type="text" wire:model="patient_class" placeholder="Kelas Pasien"
                            label="Kelas Pasien" description="Kelas pasien untuk klaim" />
                    </div>

                    @if($confirmPatient == false)
                        <div class="flex justify-end mt-4 gap-3">
                            <flux:button wire:click="cancelForm" variant="ghost" icon="x-circle"
                                class="w-1/5 px-12 font-medium">
                                Batal
                            </flux:button>
                            <flux:button wire:click="confirmPatientTrue" variant="primary" icon="check-circle">
                                Validasi
                            </flux:button>
                        </div>
                    @endif
                </div>

                <!-- SEP Details Section -->
                <div wire:show='confirmPatient' wire:transition.scale.origin.top.duration.300ms
                    class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl shadow-lg">
                    <div class="flex items-center gap-2">
                        <flux:icon.archive-box variant="solid" class="text-sky-600 dark:text-sky-200" />
                        <h1 class="text-2xl font-bold">Input Dokumen File Penunjang</h1>
                    </div>
                    <hr class="mt-4 mb-10 border-gray-300 dark:border-gray-600">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
                        <!-- Resume File -->
                        <flux:field>
                            <flux:label>
                                <flux:icon.document-text variant="solid"
                                    class="text-pink-600 dark:text-pink-400 mr-1.5" />
                                File Awal Medis IGD
                            </flux:label>
                            <flux:description>File Awal Medis IGD untuk klaim</flux:description>
                            <flux:input type="file" wire:model="resumeFile" accept=".pdf"
                                placeholder="Unggah File Awal Medis" />
                        </flux:field>

                        <!-- Billing File -->
                        <flux:field>
                            <flux:label>
                                <flux:icon.document-currency-dollar variant="solid"
                                    class="text-amber-600 dark:text-amber-300 mr-1.5" />
                                Billing Rawat Jalan
                            </flux:label>
                            <flux:description>File Billing Rawat Jalan untuk klaim</flux:description>
                            <flux:input type="file" wire:model="billingFile" accept=".pdf,.jpg,.png"
                                placeholder="Unggah File Billing" />
                        </flux:field>
                    </div>

                    <!-- Actions -->
                    <div class="flex md:flex-row justify-end mt-10 gap-4">
                        <flux:button wire:click="cancelForm" variant="filled" icon="x-circle"
                            class="w-1/5 px-12 font-medium">
                            Batal
                        </flux:button>
                        <flux:button wire:click="submit" variant="primary" icon="arrow-down-tray"
                            class="w-1/5 px-12 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-lg transition-colors duration-200 shadow hover:shadow-lg">
                            Simpan Dokumen
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Section -->
        @if($showUploadedData == false)
            <div class="max-w-4xl mx-auto p-10 bg-zinc-50 dark:bg-zinc-900  rounded-xl shadow-lg">
                <div aria-labelledby="file-upload-heading" class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">
                    <h3 id="file-upload-heading" class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        <i class="fas fa-file-upload mr-2 text-blue-600 dark:text-blue-400"></i>
                        Unggah Dokumen Klaim (PDF)
                    </h3>
                    <div class="space-y-4">
                        <label for="file_upload_input"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih file PDF:</label>
                        <div
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625..." />
                                </svg>
                                <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                    <label for="file_upload_input_livewire"
                                        class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Unggah file</span>
                                        <input id="file_upload_input_livewire" wire:model="sepFile" type="file"
                                            class="sr-only" accept=".pdf">
                                    </label>
                                    <p class="pl-1">di sini</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PDF hingga 2MB per file</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Loading Overlays -->
    <div wire:loading.flex wire:target="sepFile,resumeFile,billingFile"
        class="fixed inset-0 z-100 bg-neutral-900/60 flex items-center justify-center backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 text-center animate-fade-in">
            <svg class="h-12 w-12 animate-spin text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-amber-600 dark:text-amber-200 font-semibold text-lg">Memproses file PDF, mohon tunggu...</p>
        </div>
    </div>

    <div wire:loading.flex wire:target="cancelForm"
        class="fixed inset-0 z-100 bg-neutral-900/60 flex items-center justify-center backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 text-center animate-fade-in">
            <svg class="h-12 w-12 animate-spin text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-red-600 dark:text-red-200 font-semibold text-lg">Membatalkan dokumen...</p>
        </div>
    </div>
</div>