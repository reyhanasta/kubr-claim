<div>
    <div>
        <!-- Header -->
        <div class=" mb-5 text-center">
            <flux:heading size="xl" level="1" class="text-amber">BPJS Claim Submission</flux:heading>
            <flux:subheading size="md" class="text-gray-200">Please fill in the patient's details below.
            </flux:subheading>
        </div>
        @if(empty($sepFile))
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
        {{-- Bagian Unggah File Awal --}}

        @endif

        @if (!empty($sepFile))
        <div class="max-w-4xl mx-auto p-8 bg-gray-700 text-gray-100 rounded-xl shadow-lg">
            <form wire:submit.prevent="submit" wire:loading.attr="disabled" wire:target="submit" class="space-y-4">
                <!-- Patient Info Section -->
                <div class="grid grid-cols-4 gap-6">
                    <!-- Nomor RM -->
                    <div class="col-span-1">
                        <flux:input name="medical_record_number" label="Nomor RM" type="text"
                            wire:model="medical_record_number" icon:trailing="{{ $rmIcon }}" placeholder="Nomor RM" {{--
                            wire:change="searchPatient" --}} badge="Wajib diisi" />
                    </div>
                    <!-- Nama Pasien -->
                    <div class="col-span-2">
                        <flux:input type="text" variant="filled" icon="user" wire:model.debounce.500ms="patient_name"
                            readonly class="cursor-not-allowed mt-1.5" label="Nama Pasien"
                            placeholder="Terisi Otomatis" />
                    </div>
                    <div class="col-span-1">
                        <flux:input type="text" variant="filled" icon="credit-card"
                            wire:model.debounce.500ms="bpjs_serial_number" readonly class="cursor-not-allowed mt-1.5"
                            label="Nomor Kartu BPJS" placeholder="Terisi Otomatis" copyable />
                    </div>
                </div>
                <!-- Additional Info Section -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- Nomor SEP -->
                    <div class="col-span-2">
                        <flux:input type="text" icon="document-text" wire:model.debounce.500ms="sep_number"
                            placeholder="Nomor SEP" label="Nomor SEP" badge="Wajib diisi" readonly />
                    </div>
                    <!-- Tanggal Dokumen -->
                    <div>
                        <flux:input type="date" wire:model="sep_date" placeholder="Tanggal SEP" label="Tanggal SEP"
                            badge="Wajib diisi" />
                    </div>
                </div>
                <!-- Additional Files (Awal Medis & Billing) -->
                <div id="add-files" class=" gap-6">
                    <!-- Awal Medis -->
                    <div class="">
                        <flux:input type="file" label="File Awal Medis" wire:model="resumeFile" accept=".pdf"
                            placeholder="Unggah File Awal Medis" />
                        {{ $scanned_docs['resume'] ?? '' }}
                        <!-- Awal Medis Preview -->
                        @if($scanned_docs['resume'] ?? false)
                        <div class="flex items-center gap-4 p-4 bg-gray-700 border border-gray-600 rounded-lg">
                            <!-- Inline PDF Preview with rotation support -->
                            <div
                                class="w-64 h-80 overflow-hidden border border-gray-500 rounded bg-white flex-shrink-0 shadow-sm relative">
                                <div class="w-full h-full"
                                    style="transform: rotate({{ $rotations['resume'] ?? 0 }}deg); transform-origin: center center; transition: transform 0.3s ease;">
                                    <iframe src="{{ $previewUrls['resume'] }}#toolbar=0&navpanes=0&scrollbar=0"
                                        class="w-full h-full" frameborder="0">
                                    </iframe>
                                </div>
                            </div>

                            <!-- File Name -->
                            <div class="flex-1 truncate text-sm text-gray-100">
                                {{ $scanned_docs['resume']->getClientOriginalName() }}
                            </div>
                            <!-- Rotation Controls -->
                            <flux:button icon="arrow-uturn-right" size="xs" variant="ghost"
                                wire:click.prevent="rotateFile()" title="Putar PDF">

                            </flux:button>

                            <flux:button icon="eye" size="xs" variant="ghost" wire:click.prevent="previewFile(1)"
                                title="Preview" />
                        </div>
                        @endif
                    </div>
                    <!-- Awal Medis Preview -->
                </div>

                <div class="col-span-1">
                    <flux:input type="file" label="File Billing" wire:model="billingFile" accept=".pdf,.jpg,.png"
                        placeholder="Unggah File Billing" />
                </div>
                <!-- Awal Medis Preview -->
                <div class="col-span-1">

                </div>
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

    <div wire:loading.flex wire:target="new_docs"
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