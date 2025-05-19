
<div class="max-w-4xl mx-auto p-8 bg-gray-700 text-gray-100 rounded-xl shadow-lg">
     
    <!-- Header -->
    <div class="mb-8 text-center">
        <flux:heading size="xl" level="1" class="text-amber">BPJS Claim Submission</flux:heading>
        <flux:subheading size="md" class="text-gray-200">Please fill in the patient's details below.</flux:subheading>
    </div>
   

    <form wire:submit.prevent="submit" class="space-y-8">
        <!-- Patient Info Section -->
        <div class="grid grid-cols-4 md:grid-cols-4 gap-6">
            <!-- Nomor RM -->
            <div class="col-span-1">
                <flux:input name="no_rm" label="Nomor RM" type="text" wire:model.lazy="no_rm"
                    icon:trailing="{{ $rmIcon }}" placeholder="Nomor RM" wire:change="searchPatient" 
                    badge="Wajib diisi" />
            </div>
            <!-- Nama Pasien -->
            <div class="col-span-2">
                <flux:input type="text" variant="filled" icon="user" wire:model.debounce.500ms="patient_name" readonly
                    class="cursor-not-allowed mt-1.5 " label="Nama Pasien" placeholder="Terisi Otomatis" />
            </div>
            <div class="col-span-1">
                <flux:input type="text" variant="filled" icon="credit-card" wire:model.debounce.500ms="no_kartu_bpjs"
                    readonly class="cursor-not-allowed mt-1.5" label="Nomor Kartu BPJS" placeholder="Terisi Otomatis"
                    copyable />
            </div>
        </div>

        <!-- Additional Info Section -->
        <div class="grid grid-cols-4 md:grid-cols-4 gap-6">
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
        <div>
            <flux:input type="file" label="Upload Scanned Documents" wire:model="scanned_docs" multiple
                accept=".pdf,.jpg,.png" placeholder="Upload Scanned Documents" />
        </div>

        <!-- File List with Reordering -->
        @if($scanned_docs)
        <div class="mt-4 space-y-2">
            @foreach($scanned_docs as $index => $doc)
            <div class="flex items-center gap-4 p-4 bg-gray-700 border border-gray-600 rounded-lg">
                <span class="text-amber-300 font-semibold">Page {{ $loop->iteration }}</span>
                <!-- Reorder Buttons -->
                @if($index > 0)
                <flux:button size="xs" icon="arrow-up" variant="primary" wire:click.prevent="moveUp({{ $index }})">
                </flux:button>
                @endif
                @if($index < count($scanned_docs) - 1) <flux:button size="xs" icon="arrow-down" variant="primary"
                    wire:click.prevent="moveDown({{ $index }})">
                    </flux:button>
                    @endif

                    <!-- File Info -->
                    <span class="flex-1 truncate">
                        {{ $doc->getClientOriginalName() }}
                    </span>

                    <!-- Preview Button -->
                    <flux:button icon="eye" variant="ghost" wire:click.prevent="previewFile({{ $index }})">
                    </flux:button>
                    <!-- Remove Button -->
                    <flux:button icon="trash" variant="subtle" class=" hover:text-red-500"
                        wire:click.prevent="removeFile({{ $index }})">
                    </flux:button>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Preview Modal -->
        @if($showPreviewModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 p-4 overflow-auto"
            wire:click.self="closePreviewModal">
            <div class="relative bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="flex-shrink-0 flex justify-between items-center px-6 py-4 border-b border-gray-600">
                    <flux:heading size="md" level="3" class="text-xl font-semibold text-amber-300">PDF Preview
                    </flux:heading>
                    <flux:button icon="x-mark" variant="subtle" wire:click="closePreviewModal" />
                </div>

                <!-- Modal Content -->
                <div class="flex-1 min-h-0 relative">
                    @if($this->currentPreviewUrl)
                    <iframe src="{{ $this->currentPreviewUrl }}" class="w-full h-full border-0" frameborder="0"
                        style="min-height: 80vh;">
                    </iframe>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Submit Button -->
        <div class="text-right">
            <flux:button variant="primary" type="submit" icon="arrow-down-tray"
                class="active:scale-110 px-6 py-3 bg-emerald-500 text-white font-semibold rounded-lg transition-all duration-300 ease-in-out shadow-md hover:bg-emerald-600 hover:shadow-emerald-500/30 focus:outline-none focus:ring-4 focus:ring-emerald-500">
                Simpan</flux:button>
        </div>
    </form>
</div>