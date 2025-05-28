<div>
    <div>
        <!-- Header -->
        <div class="mb-3 text-center">
            <flux:heading size="xl" level="1" class="text-amber">BPJS Claim Submission</flux:heading>
            <flux:subheading size="md" class="text-gray-200">Please fill in the patient's details below.
            </flux:subheading>
        </div>
        @if (!empty($sepFile))
        <div class="max-w-4xl mx-auto p-2 rounded-xl shadow-lg">
            <!-- PDF Preview for 3-section format (similar to A5 proportions) -->
            <div class="w-full bg-white rounded-lg overflow-hidden shadow-lg relative" style="padding-top: 34%">
                <iframe src="{{ $previewUrls[0] ?? '' }}#toolbar=0&navpanes=0&scrollbar=0"
                    class="absolute top-0 left-0 w-full h-full" frameborder="0">
                </iframe>
            </div>
        </div>
        <form wire:submit.prevent="submit" wire:target="submit" class="space-y-4">
            <!-- Patient Info Section -->
            <div class="max-w-4xl mx-auto p-6 bg-gray-700 text-gray-100 rounded-xl shadow-lg">
                <h1 class="text-2xl font-bold">
                    Data Pasien</h1>
                <hr class="my-4">
                <!-- SIMRS Data Display -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                    <!-- Medical Record - Vibrant Blue -->
                    <div
                        class="group  bg-gray-800 p-4 rounded-xl shadow-lg border-l-4 border-blue-400 hover:bg-gray-750 transition-all duration-200 relative overflow-hidden">
                        <div class="flex items-start gap-3">
                            <div class="bg-blue-900/30 p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                                </svg>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-blue-300 uppercase tracking-wider mb-1">Nomor
                                    RM</label>
                                <div class="text-white font-mono text-lg font-medium tracking-tight">
                                    {{ $simrs_rm_number }}
                                </div>
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-2 -right-2 text-blue-400 opacity-10 group-hover:opacity-20 transition-opacity duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>

                    <!-- Patient Name - Energetic Orange -->
                    <div
                        class="group md:col-span-2 bg-gray-800 p-4 rounded-xl shadow-lg border-l-4 border-orange-400 hover:bg-gray-750 transition-all duration-200 relative overflow-hidden">
                        <div class="flex items-start gap-3">
                            <div class="bg-orange-900/30 p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-orange-300 uppercase tracking-wider mb-1">Nama
                                    Pasien SIMRS</label>
                                <div class="text-white text-lg font-medium">
                                    {{ $patient_name }}
                                </div>
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-2 -right-2 text-orange-400 opacity-10 group-hover:opacity-20 transition-opacity duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>

                    <!-- BPJS Number - Fresh Teal -->
                    <div
                        class="group bg-gray-800 p-4 pr-5 rounded-xl shadow-lg border-l-4 border-teal-400 hover:bg-gray-750 transition-all duration-200 relative overflow-hidden">
                        <div class="flex items-start gap-3 ">
                            <div class="bg-teal-900/30 p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-400"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm11 1H6v8l4-2 4 2V6z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-teal-300 uppercase tracking-wider mb-1">Nomor
                                    Kartu </label>
                                <div class="text-white font-mono text-lg font-medium tracking-tight">
                                    {{ $bpjs_serial_number }}
                                </div>
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-2 -right-2 text-teal-400 opacity-10 group-hover:opacity-20 transition-opacity duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                </div>
                @if($patientValidated == false)
                <!-- SEP Input Section -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <flux:input name="medical_record_number" label="Nomor RM SEP" type="text"
                            wire:model="medical_record_number" icon:trailing="{{ $rmIcon }}" placeholder="Nomor RM" />
                    </div>
                    <div class="md:col-span-2">
                        <flux:input type="text" variant="filled" icon="user" wire:model.debounce.500ms="patient_name"
                            readonly class="cursor-not-allowed" label="Nama Pasien SEP" placeholder="Terisi Otomatis" />
                    </div>
                    <div>
                        <flux:input type="text" variant="filled" icon="credit-card"
                            wire:model.debounce.500ms="bpjs_serial_number" readonly class="cursor-not-allowed"
                            label="Nomor Kartu BPJS SEP" placeholder="Terisi Otomatis" copyable />
                    </div>
                </div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                    <div class="col-span-1">
                        <flux:input type="text" icon="document-text" wire:model.debounce.500ms="sep_number"
                            placeholder="Nomor SEP" label="Nomor SEP"
                            description="Nomor SEP yang akan digunakan untuk klaim" />
                    </div>
                    <div class="col-span-1">
                        <flux:input type="date" wire:model="sep_date" placeholder="Tanggal SEP" label="Tanggal SEP"
                            description="Tanggal SEP yang akan digunakan untuk klaim" />
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    @if($confirmPatient == false)
                    <flux:button variant="primary" wire:click.prevent="$set('confirmPatient', true)"
                        class="w-full md:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-lg transition-colors duration-200 shadow hover:shadow-lg"
                        icon="check-circle">
                        Validasi
                    </flux:button>
                    @endif
                </div>
            </div>
            @if($confirmPatient)
            <!-- SEP Details Section -->
            <div wire:transition.opacity class="max-w-4xl mx-auto p-6 bg-gray-700 text-gray-100 rounded-xl shadow-lg">
                <div class="flex items-center gap-2">
                    <flux:icon.archive-box variant="solid" class="dark:text-sky-200" />
                    <h1 class="text-2xl font-bold">Input Dokumen File Klaim</h1>
                </div>
                <hr class="mt-4 mb-10 ">
                <!-- File Upload Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
                    <!-- Medical Resume File -->
                    <div>
                        <flux:field>
                            <flux:label>
                                <flux:icon.document-text variant="solid" class="dark:text-pink-400 mr-1.5" /> File Awal
                                Medis IGD
                            </flux:label>
                            <flux:description>File Awal Medis IGD yang akan digunakan untuk klaim</flux:description>
                            <flux:input type="file" wire:model="resumeFile" accept=".pdf"
                                placeholder="Unggah File Awal Medis" />
                        </flux:field>

                    </div>

                    <!-- Billing File -->
                    <div>
                        <flux:field>
                            <flux:label>
                                <flux:icon.document-currency-dollar variant="solid"
                                    class="dark:text-amber-300 mr-1.5" />
                                Billing Rawat Jalan
                            </flux:label>
                            <flux:description>File Billing Rawat Jalan yang akan digunakan untuk klaim
                            </flux:description>
                            <flux:input type="file" wire:model="billingFile" accept=".pdf,.jpg,.png"
                                placeholder="Unggah File Billing" />
                        </flux:field>


                        <!-- Similar preview can be added for billing file -->
                    </div>
                </div>
                <!-- Form Actions -->
                <div class="flex md:flex-row justify-between items-center mt-10">
                    <flux:button wire:click="submit" variant="primary" icon="arrow-down-tray"
                        class="w-full px-12 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-lg transition-colors duration-200 shadow hover:shadow-lg">
                        Simpan Dokumen
                    </flux:button>
                </div>
            </div>
            @endif
        </form>
        @else
        <div class="max-w-4xl mx-auto p-8">
            <div aria-labelledby="file-upload-heading" class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h3 id="file-upload-heading" class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-file-upload mr-2 text-blue-500"></i>Unggah Dokumen Klaim (PDF)
                </h3>

                <div class="space-y-4">
                    <label for="file_upload_input" class="block text-sm font-medium text-gray-700 mb-2">Pilih file
                        PDF:</label>
                    {{-- <label for="file_upload_input" class="block text-sm font-medium text-gray-700 mb-2">Pilih atau
                        seret
                        file ke sini:</label> --}}
                    <div
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill=" none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>


                            <div class="flex text-sm text-gray-600">
                                <label for="file_upload_input_livewire"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Unggah file</span>
                                    <input id="file_upload_input_livewire" wire:model="sepFile" type="file"
                                        class="sr-only" accept=".pdf">
                                </label>
                                {{-- <p class="pl-1">atau seret dan lepas</p> --}}
                                <p class="pl-1">di sini</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF hingga 2MB per file</p>
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
            <svg class="h-12 w-12 animate-spin text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-amber-100 font-semibold text-lg">Memproses file PDF, mohon tunggu...</p>
        </div>
    </div>
    {{-- <div wire:loading wire:target="submit"
        class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 text-center">
            <svg class="h-12 w-12 animate-spin text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-emerald-100 font-semibold text-lg">Menyimpan klaim BPJS...</p>
        </div>
    </div> --}}
</div>