<div class="min-h-screen py-8 px-4">
    {{-- Offline Indicator --}}
    <div wire:offline class="fixed top-4 right-4 z-50 animate-pulse">
        <div class="flex items-center gap-2 bg-rose-500 text-white px-4 py-3 rounded-xl shadow-lg backdrop-blur-sm">
            <flux:icon.wifi class="w-5 h-5" />
            <span class="text-sm font-medium">Tidak ada koneksi</span>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        {{-- Header Section --}}
        {{-- Main Form After SEP Upload --}}
        @if($showUploadedData)
            <form wire:submit.prevent="submit" class="space-y-6 animate-fade-in">
                {{-- PDF Preview Section --}}
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 transform hover:shadow-2xl transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-sage-600 to-sage-500 dark:from-sage-700 dark:to-sage-600 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <flux:icon.document-magnifying-glass class="w-6 h-6 text-white" />
                                <flux:heading size="lg" class="text-white">Preview Dokumen SEP</flux:heading>
                            </div>
                            <flux:badge color="white" size="lg">PDF</flux:badge>
                        </div>
                    </div>
                    <div class="relative bg-slate-100 dark:bg-slate-900" style="padding-top: 34%">
                        <iframe src="{{ $previewUrls['sepFile'] ?? '' }}#zoom=120&toolbar=0&navpanes=0&scrollbar=0"
                            class="absolute top-0 left-0 w-full h-full border-0" loading="lazy">
                        </iframe>
                    </div>
                </div>

                {{-- Patient Information Card --}}
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 transform hover:shadow-2xl transition-shadow duration-300">
                    <div
                        class="bg-gradient-to-r from-sage-700 via-sage-600 to-sage-500 dark:from-sage-800 dark:via-sage-700 dark:to-sage-600 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:heading size="lg" class="text-white flex items-center gap-2">
                                    <flux:icon.user-circle class="w-6 h-6" />
                                    Data Pasien
                                </flux:heading>
                                <flux:text size="sm" class="text-white/80 mt-1">
                                    Informasi pasien dari dokumen SEP
                                </flux:text>
                            </div>
                            <flux:badge color="white" size="lg">
                                {{ $jenis_rawatan }}
                            </flux:badge>
                        </div>
                    </div>

                    <div class="p-6">
                        {{-- Patient Info Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            {{-- Nomor RM --}}
                            <div
                                class="group relative overflow-hidden bg-gradient-to-br from-sage-50 to-sage-100 dark:from-sage-900/20 dark:to-sage-800/20 p-5 rounded-xl border-l-4 border-sage-500 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-sage-500/10 rounded-full"></div>
                                <flux:label
                                    class="text-xs font-bold text-sage-700 dark:text-sage-300 uppercase tracking-wide mb-2 flex items-center gap-2">
                                    <flux:icon.hashtag class="w-4 h-4" />
                                    Nomor RM
                                </flux:label>
                                <div class="font-mono text-xl font-bold text-slate-900 dark:text-white relative z-10">
                                    {{ $medical_record_number }}
                                </div>
                            </div>

                            {{-- Nama Pasien --}}
                            <div
                                class="group relative overflow-hidden bg-gradient-to-br from-sage-100 to-sage-200 dark:from-sage-900/30 dark:to-sage-800/30 p-5 rounded-xl border-l-4 border-sage-600 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 md:col-span-2">
                                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-sage-600/10 rounded-full"></div>
                                <flux:label
                                    class="text-xs font-bold text-sage-700 dark:text-sage-300 uppercase tracking-wide mb-2 flex items-center gap-2">
                                    <flux:icon.user class="w-4 h-4" />
                                    Nama Pasien
                                </flux:label>
                                <div class="text-xl font-bold text-slate-900 dark:text-white relative z-10 truncate">
                                    {{ $patient_name }}
                                </div>
                            </div>

                            {{-- Nomor BPJS --}}
                            <div
                                class="group relative overflow-hidden bg-gradient-to-br from-amber-50 to-orange-100 dark:from-amber-900/20 dark:to-orange-800/20 p-5 rounded-xl border-l-4 border-amber-500 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-amber-500/10 rounded-full">
                                </div>
                                <flux:label
                                    class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wide mb-2 flex items-center gap-2">
                                    <flux:icon.identification class="w-4 h-4" />
                                    No. BPJS
                                </flux:label>
                                <div class="font-mono text-xl font-bold text-slate-900 dark:text-white relative z-10">
                                    {{ $bpjs_serial_number }}
                                </div>
                            </div>
                        </div>

                        {{-- Claim Details Form --}}
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-6 border-t border-slate-200 dark:border-slate-700">
                            <flux:field>
                                <flux:label class="flex items-center gap-2">
                                    <flux:icon.document-text class="w-4 h-4" />
                                    Nomor SEP
                                </flux:label>
                                <flux:input type="text" wire:model="sep_number" readonly
                                    class="bg-slate-50 dark:bg-slate-900" />
                                @error('sep_number')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label class="flex items-center gap-2">
                                    <flux:icon.calendar class="w-4 h-4" />
                                    {{ $sep_date_label }}
                                </flux:label>
                                {{-- Use wire:model.live for RI so supporting docs form appears immediately --}}
                                <flux:input type="date" wire:model.live="sep_date" />
                                @error('sep_date')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label class="flex items-center gap-2">
                                    <flux:icon.shield-check class="w-4 h-4" />
                                    Kelas Pasien
                                </flux:label>
                                <flux:input type="text" wire:model="patient_class" />
                                @error('patient_class')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label class="flex items-center gap-2">
                                    <flux:icon.building-office-2 class="w-4 h-4" />
                                    Jenis Rawatan
                                </flux:label>
                                <flux:input type="text" wire:model="jenis_rawatan" readonly
                                    class="bg-slate-50 dark:bg-slate-900" />
                            </flux:field>
                        </div>
                    </div>
                </div>

                {{-- Notice for Rawat Inap: Fill discharge date first --}}
                @if ($jenis_rawatan === 'RI' && !$this->canShowSupportingDocuments)
                    <div
                        class="bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-300 dark:border-amber-700 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-xl">
                                <flux:icon.exclamation-triangle class="w-8 h-8 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-grow">
                                <flux:heading size="lg" class="text-amber-800 dark:text-amber-200 mb-2">
                                    Isi Tanggal Pulang Terlebih Dahulu
                                </flux:heading>
                                <flux:text class="text-amber-700 dark:text-amber-300">
                                    Untuk klaim Rawat Inap, silakan isi <strong>Tanggal Pulang</strong> pada form di atas
                                    terlebih dahulu
                                    sebelum mengupload dokumen pendukung.
                                </flux:text>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Documents Upload Section --}}
                @if ($this->canShowSupportingDocuments)
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                        <div
                            class="bg-gradient-to-r from-sage-700 via-sage-600 to-sage-500 dark:from-sage-800 dark:via-sage-700 dark:to-sage-600 p-6">
                            <flux:heading size="lg" class="text-white flex items-center gap-2">
                                <flux:icon.folder-open class="w-6 h-6" />
                                Dokumen Pendukung
                            </flux:heading>
                            <flux:text size="sm" class="text-white/80 mt-1">
                                Upload file Resume Medis, Billing, dan dokumen tambahan
                            </flux:text>
                        </div>

                        <div class="p-6 space-y-6">
                            {{-- Main Required Documents --}}
                            <div class="space-y-4">


                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Resume File --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="p-2 bg-sage-100 dark:bg-sage-900/30 rounded-lg">
                                                <flux:icon.document-text class="w-5 h-5 text-sage-600 dark:text-sage-400" />
                                            </div>
                                            <div>
                                                <flux:heading size="sm">Resume Medis</flux:heading>
                                                <flux:text size="sm" class="text-rose-600 dark:text-rose-400">*Wajib</flux:text>
                                            </div>
                                        </div>
                                        <flux:input type="file" wire:model="resumeFile" accept=".pdf"
                                            label="Upload Resume Medis (PDF)" />
                                        @error('resumeFile')
                                            <div
                                                class="flex items-center gap-2 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 dark:border-rose-800">
                                                <flux:icon.exclamation-circle
                                                    class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                                                <flux:text size="sm" class="text-rose-900 dark:text-rose-100">{{ $message }}
                                                </flux:text>
                                            </div>
                                        @enderror
                                        @if($resumeFile)
                                            <div
                                                class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                <flux:icon.check-circle
                                                    class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                                                <flux:text size="sm"
                                                    class="text-emerald-900 dark:text-emerald-100 truncate flex-grow">
                                                    {{ is_object($resumeFile) ? $resumeFile->getClientOriginalName() : 'Resume Medis' }}
                                                </flux:text>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Billing File --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="p-2 bg-sage-100 dark:bg-sage-900/30 rounded-lg">
                                                <flux:icon.document-currency-dollar
                                                    class="w-5 h-5 text-sage-600 dark:text-sage-400" />
                                            </div>
                                            <div>
                                                <flux:heading size="sm">File Billing</flux:heading>
                                                <flux:text size="sm" class="text-rose-600 dark:text-rose-400">*Wajib</flux:text>
                                            </div>
                                        </div>
                                        <flux:input type="file" wire:model="billingFile" accept=".pdf,.jpg,.png"
                                            label="Upload Billing (PDF/JPG/PNG)" />
                                        @error('billingFile')
                                            <div
                                                class="flex items-center gap-2 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 dark:border-rose-800">
                                                <flux:icon.exclamation-circle
                                                    class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                                                <flux:text size="sm" class="text-rose-900 dark:text-rose-100">{{ $message }}
                                                </flux:text>
                                            </div>
                                        @enderror
                                        @if($billingFile)
                                            <div
                                                class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                <flux:icon.check-circle
                                                    class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                                                <flux:text size="sm"
                                                    class="text-emerald-900 dark:text-emerald-100 truncate flex-grow">
                                                    {{ is_object($billingFile) ? $billingFile->getClientOriginalName() : 'Billing' }}
                                                </flux:text>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Optional Documents --}}
                            <div class="pt-6 border-t border-slate-200 dark:border-slate-700 space-y-4">
                                <div class="flex items-center gap-2">
                                    <flux:icon.plus-circle class="w-5 h-5 text-slate-400" />
                                    <flux:heading size="sm" class="text-slate-600 dark:text-slate-300">Dokumen Tambahan
                                        (Opsional)</flux:heading>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                                    {{-- SEP RJ File (only for RI) --}}
                                    @if ($jenis_rawatan === 'RI')
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-2">
                                                <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                                                    <flux:icon.document-text
                                                        class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                                </div>
                                                <flux:heading size="sm">SEP Rawat Jalan</flux:heading>
                                            </div>
                                            <flux:input type="file" wire:model="sepRJFile" accept=".pdf"
                                                label="Upload SEP RJ (Opsional)" />
                                            @error('sepRJFile')
                                                <div
                                                    class="flex items-center gap-2 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 dark:border-rose-800">
                                                    <flux:icon.exclamation-circle
                                                        class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                                                    <flux:text size="sm" class="text-rose-900 dark:text-rose-100">{{ $message }}
                                                    </flux:text>
                                                </div>
                                            @enderror
                                            @if($sepRJFile)
                                                <div
                                                    class="flex items-center gap-2 p-3 bg-sky-50 dark:bg-sky-900/20 rounded-lg border border-sky-200 dark:border-sky-800">
                                                    <flux:icon.check-circle
                                                        class="w-5 h-5 text-sky-600 dark:text-sky-400 flex-shrink-0" />
                                                    <flux:text size="sm" class="text-sky-900 dark:text-sky-100 truncate flex-grow">
                                                        {{ is_object($sepRJFile) ? $sepRJFile->getClientOriginalName() : 'SEP RJ' }}
                                                    </flux:text>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- LIP File --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="p-2 bg-violet-100 dark:bg-violet-900/30 rounded-lg">
                                                <flux:icon.document-plus class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                            </div>
                                            <div class="flex-grow">
                                                <flux:heading size="sm">Dokumen LIP</flux:heading>
                                                <flux:text size="xs" class="text-slate-500 dark:text-slate-400">
                                                    Disimpan terpisah, tidak ikut di-merge
                                                </flux:text>
                                            </div>
                                        </div>
                                        <flux:input type="file" wire:model="fileLIP" accept=".pdf"
                                            label="Upload LIP (Opsional)" />
                                        @error('fileLIP')
                                            <div
                                                class="flex items-center gap-2 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 dark:border-rose-800">
                                                <flux:icon.exclamation-circle
                                                    class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                                                <flux:text size="sm" class="text-rose-900 dark:text-rose-100">{{ $message }}
                                                </flux:text>
                                            </div>
                                        @enderror
                                        @if($fileLIP)
                                            <div
                                                class="flex items-center gap-2 p-3 bg-violet-50 dark:bg-violet-900/20 rounded-lg border border-violet-200 dark:border-violet-800">
                                                <flux:icon.check-circle
                                                    class="w-5 h-5 text-violet-600 dark:text-violet-400 flex-shrink-0" />
                                                <flux:text size="sm"
                                                    class="text-violet-900 dark:text-violet-100 truncate flex-grow">
                                                    {{ is_object($fileLIP) ? $fileLIP->getClientOriginalName() : 'LIP' }}
                                                </flux:text>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Hasil Lab 1 (PDF only) --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="p-2 bg-rose-100 dark:bg-rose-900/30 rounded-lg">
                                                <flux:icon.beaker class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                                            </div>
                                            <div class="flex-grow">
                                                <flux:heading size="sm">Hasil Labor 1</flux:heading>
                                                <flux:text size="xs" class="text-slate-500 dark:text-slate-400">
                                                    Opsional, diikutkan dalam file gabungan
                                                </flux:text>
                                            </div>
                                        </div>
                                        <flux:input type="file" wire:model="labResultFile" accept=".pdf"
                                            label="Upload Hasil Lab (PDF)" />
                                        @error('labResultFile')
                                            <div
                                                class="flex items-center gap-2 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 dark:border-rose-800">
                                                <flux:icon.exclamation-circle
                                                    class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                                                <flux:text size="sm" class="text-rose-900 dark:text-rose-100">{{ $message }}
                                                </flux:text>
                                            </div>
                                        @enderror
                                        @if($labResultFile)
                                            <div
                                                class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                <flux:icon.check-circle
                                                    class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                                                <flux:text size="sm"
                                                    class="text-emerald-900 dark:text-emerald-100 truncate flex-grow">
                                                    {{ is_object($labResultFile) ? $labResultFile->getClientOriginalName() : 'Hasil Lab' }}
                                                </flux:text>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Hasil Lab 2 (PDF only) --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="p-2 bg-rose-100 dark:bg-rose-900/30 rounded-lg">
                                                <flux:icon.beaker class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                                            </div>
                                            <div class="flex-grow">
                                                <flux:heading size="sm">Hasil Labor 2</flux:heading>
                                                <flux:text size="xs" class="text-slate-500 dark:text-slate-400">
                                                    Opsional, diikutkan dalam file gabungan
                                                </flux:text>
                                            </div>
                                        </div>
                                        <flux:input type="file" wire:model="labResultFile2" accept=".pdf"
                                            label="Upload Hasil Lab (PDF)" />
                                        @error('labResultFile2')
                                            <div
                                                class="flex items-center gap-2 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 dark:border-rose-800">
                                                <flux:icon.exclamation-circle
                                                    class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" />
                                                <flux:text size="sm" class="text-rose-900 dark:text-rose-100">{{ $message }}
                                                </flux:text>
                                            </div>
                                        @enderror
                                        @if($labResultFile2)
                                            <div
                                                class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                <flux:icon.check-circle
                                                    class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                                                <flux:text size="sm"
                                                    class="text-emerald-900 dark:text-emerald-100 truncate flex-grow">
                                                    {{ is_object($labResultFile2) ? $labResultFile2->getClientOriginalName() : 'Hasil Lab 2' }}
                                                </flux:text>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Upload Progress Indicator --}}
                            <div class="pt-6 border-t border-slate-200 dark:border-slate-700">
                                <div class="flex items-center justify-between mb-3">
                                    <flux:text size="sm" class="text-slate-600 dark:text-slate-400 font-medium">Status Upload
                                    </flux:text>
                                    <flux:text size="sm" class="text-slate-500 dark:text-slate-400">
                                        {{ collect([$resumeFile, $billingFile])->filter()->count() }}/2 wajib
                                    </flux:text>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                    <div
                                        class="text-center p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border {{ $resumeFile ? 'border-emerald-300 dark:border-emerald-700' : 'border-slate-200 dark:border-slate-700' }}">
                                        <div
                                            class="text-2xl font-bold {{ $resumeFile ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                            {{ $resumeFile ? '✓' : '○' }}
                                        </div>
                                        <flux:text size="xs" class="text-slate-600 dark:text-slate-400 mt-1">Resume</flux:text>
                                    </div>
                                    <div
                                        class="text-center p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border {{ $billingFile ? 'border-emerald-300 dark:border-emerald-700' : 'border-slate-200 dark:border-slate-700' }}">
                                        <div
                                            class="text-2xl font-bold {{ $billingFile ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                            {{ $billingFile ? '✓' : '○' }}
                                        </div>
                                        <flux:text size="xs" class="text-slate-600 dark:text-slate-400 mt-1">Billing</flux:text>
                                    </div>
                                    @if($jenis_rawatan === 'RI')
                                        <div
                                            class="text-center p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border {{ $sepRJFile ? 'border-sky-300 dark:border-sky-700' : 'border-slate-200 dark:border-slate-700' }}">
                                            <div
                                                class="text-2xl font-bold {{ $sepRJFile ? 'text-sky-600 dark:text-sky-400' : 'text-slate-400' }}">
                                                {{ $sepRJFile ? '✓' : '○' }}
                                            </div>
                                            <flux:text size="xs" class="text-slate-600 dark:text-slate-400 mt-1">SEP RJ</flux:text>
                                        </div>
                                    @endif
                                    <div
                                        class="text-center p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border {{ $fileLIP ? 'border-violet-300 dark:border-violet-700' : 'border-slate-200 dark:border-slate-700' }}">
                                        <div
                                            class="text-2xl font-bold {{ $fileLIP ? 'text-violet-600 dark:text-violet-400' : 'text-slate-400' }}">
                                            {{ $fileLIP ? '✓' : '○' }}
                                        </div>
                                        <flux:text size="xs" class="text-slate-600 dark:text-slate-400 mt-1">LIP</flux:text>
                                    </div>
                                    <div
                                        class="text-center p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border {{ $labResultFile ? 'border-rose-300 dark:border-rose-700' : 'border-slate-200 dark:border-slate-700' }}">
                                        <div
                                            class="text-2xl font-bold {{ $labResultFile ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                            {{ $labResultFile ? '✓' : '○' }}
                                        </div>
                                        <flux:text size="xs" class="text-slate-600 dark:text-slate-400 mt-1">Lab 1
                                        </flux:text>
                                    </div>
                                    <div
                                        class="text-center p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border {{ $labResultFile2 ? 'border-rose-300 dark:border-rose-700' : 'border-slate-200 dark:border-slate-700' }}">
                                        <div
                                            class="text-2xl font-bold {{ $labResultFile2 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                            {{ $labResultFile2 ? '✓' : '○' }}
                                        </div>
                                        <flux:text size="xs" class="text-slate-600 dark:text-slate-400 mt-1">Lab 2
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div
                    class="flex items-center justify-between gap-4 bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 border border-slate-200 dark:border-slate-700">
                    <flux:button variant="ghost" wire:click="cancelForm" icon="arrow-left" type="button"
                        class="hover:bg-slate-100 dark:hover:bg-slate-700">
                        Kembali
                    </flux:button>

                    <div class="flex items-center gap-3">
                        @if(!$this->canShowSupportingDocuments)
                            <div
                                class="flex items-center gap-2 text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-4 py-2 rounded-lg">
                                <flux:icon.exclamation-triangle class="w-5 h-5" />
                                <flux:text size="sm" class="font-medium">Isi tanggal pulang terlebih dahulu</flux:text>
                            </div>
                        @elseif(!$resumeFile || !$billingFile)
                            <div
                                class="flex items-center gap-2 text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-4 py-2 rounded-lg">
                                <flux:icon.exclamation-triangle class="w-5 h-5" />
                                <flux:text size="sm" class="font-medium">File wajib belum lengkap</flux:text>
                            </div>
                        @endif

                        <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled"
                            wire:target="submit" :disabled="!$this->canShowSupportingDocuments"
                            class="bg-gradient-to-r from-sage-600 via-sage-500 to-sage-400 hover:from-sage-700 hover:via-sage-600 hover:to-sage-500 shadow-lg px-8 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="submit">Simpan Klaim</span>
                            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                Menyimpan...
                            </span>
                        </flux:button>
                    </div>
                </div>
            </form>
        @endif

        {{-- Initial SEP Upload Section --}}
        @if(!$showUploadedData)
            <div class="mb-8 text-center animate-fade-in">
                <div
                    class="inline-block p-4 bg-gradient-to-br from-sage-600 to-sage-500 rounded-2xl mb-4 shadow-xl transform hover:scale-105 transition-transform duration-300">
                    <flux:icon.document-check class="w-12 h-12 text-white" />
                </div>
                <flux:heading size="xl" level="1"
                    class="mb-2 bg-gradient-to-r from-sage-800 via-sage-600 to-sage-500 dark:from-sage-200 dark:via-sage-400 dark:to-sage-500 bg-clip-text text-transparent">
                    Form Klaim BPJS Rawat Jalan
                </flux:heading>
                <flux:subheading class="text-slate-600 dark:text-slate-400">
                    Silakan upload dokumen SEP untuk memulai proses klaim
                </flux:subheading>
            </div>
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 transform hover:scale-[1.01] transition-transform duration-300 animate-fade-in">
                <div class="bg-gradient-to-r from-sage-600 to-sage-500 dark:from-sage-700 dark:to-sage-600 p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-white/20 rounded-xl">
                            <flux:icon.document-arrow-up class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="text-white">Upload File SEP</flux:heading>
                            <flux:text size="sm" class="text-white/80">
                                File PDF berisi informasi SEP pasien (Maksimal 2MB)
                            </flux:text>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div
                        class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-12 text-center hover:border-sage-500 dark:hover:border-sage-400 transition-all duration-300 bg-gradient-to-br from-slate-50 to-sage-50/30 dark:from-slate-900/50 dark:to-sage-900/10">
                        <div class="space-y-4">
                            <div
                                class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-sage-100 dark:bg-sage-900/30 mb-4 mx-auto">
                                <flux:icon.document-plus class="w-12 h-12 text-sage-600 dark:text-sage-400" />
                            </div>

                            <div>
                                <label for="sepFile" class="cursor-pointer">
                                    <div
                                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-sage-600 to-sage-500 hover:from-sage-700 hover:to-sage-600 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 font-medium">
                                        <flux:icon.arrow-up-tray class="w-5 h-5" />
                                        <span>Pilih File SEP</span>
                                    </div>
                                    <input id="sepFile" wire:model="sepFile" type="file" class="sr-only" accept=".pdf">
                                </label>
                            </div>

                            <div class="flex items-center justify-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                                <flux:icon.information-circle class="w-4 h-4" />
                                <span>Format: PDF | Maksimal: 2MB</span>
                            </div>
                        </div>
                    </div>

                    {{-- Validation Error for SEP File --}}
                    @error('sepFile')
                        <div
                            class="mt-6 p-4 bg-rose-50 dark:bg-rose-900/20 border-2 border-rose-200 dark:border-rose-800 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex-shrink-0 w-10 h-10 bg-rose-100 dark:bg-rose-900/30 rounded-full flex items-center justify-center">
                                    <flux:icon.exclamation-circle class="w-6 h-6 text-rose-600 dark:text-rose-400" />
                                </div>
                                <div class="flex-grow">
                                    <flux:text class="font-semibold text-rose-900 dark:text-rose-100">Error Upload</flux:text>
                                    <flux:text size="sm" class="text-rose-700 dark:text-rose-300 mt-1">{{ $message }}
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    @enderror

                    {{-- File Info Display --}}
                    @if($sepFile)
                        <div
                            class="mt-6 p-5 bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-200 dark:border-emerald-800 rounded-xl">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex-shrink-0 w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                                    <flux:icon.check-circle class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div class="flex-grow">
                                    <flux:text class="font-semibold text-emerald-900 dark:text-emerald-100">File SEP berhasil
                                        diunggah</flux:text>
                                    <flux:text size="sm" class="text-emerald-700 dark:text-emerald-300 mt-1">
                                        {{ is_object($sepFile) ? $sepFile->getClientOriginalName() : 'SEP File' }}
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Loading Overlay with Better Animation --}}
    <div wire:loading.flex
        wire:target="sepFile,resumeFile,billingFile,fileLIP,sepRJFile,labResultFile,labResultFile2,submit"
        class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center animate-fade-in">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-8 max-w-sm mx-4 text-center border border-slate-200 dark:border-slate-700">
            <div class="relative w-20 h-20 mx-auto mb-6">
                <div class="absolute inset-0 border-4 border-sage-200 dark:border-sage-800 rounded-full"></div>
                <div
                    class="absolute inset-0 border-4 border-transparent border-t-sage-600 dark:border-t-sage-400 rounded-full animate-spin">
                </div>
                <flux:icon.document-arrow-up class="absolute inset-0 m-auto w-8 h-8 text-sage-600 dark:text-sage-400" />
            </div>

            <flux:heading size="lg" class="text-slate-900 dark:text-white mb-2">
                Memproses File
            </flux:heading>
            <flux:text size="sm" class="text-slate-600 dark:text-slate-400">
                Mohon tunggu, sedang memproses dokumen...
            </flux:text>

            <div class="mt-6 flex items-center justify-center gap-2">
                <div class="w-2 h-2 bg-sage-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-2 h-2 bg-sage-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-2 h-2 bg-sage-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
        </div>
    </div>

    {{-- Inline Styles for Animations --}}
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }
    </style>
</div>