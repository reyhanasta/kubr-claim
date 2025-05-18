<div class="max-w-4xl mx-auto p-8 bg-gray-700 text-gray-100 rounded-xl shadow-lg">
    <!-- Header -->
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-amber">BPJS Claim Submission</h1>
        <p class="text-gray-400">Please fill in the patient's details below.</p>
    </div>

    <form wire:submit.prevent="submit" class="space-y-8">
        <!-- Patient Info Section -->
        <div class="grid grid-cols-3 md:grid-cols-3 gap-6">
            <!-- Nomor RM -->
            <div>
                <label class="block text-sm font-semibold text-amber-200 mb-2" for="no_rm">Nomor RM:</label>
                <input type="text" wire:model.lazy="no_rm" placeholder="Nomor RM" wire:change="searchPatient"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                @error('no_rm') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Patient Name -->
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-amber-200 mb-2">Nama Pasien:</label>
                <input type="text" wire:model.debounce.500ms="patient_name" readonly
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg cursor-not-allowed text-gray-300">
                <input type="text" wire:model="no_kartu_bpjs" readonly hidden>
            </div>
        </div>

        <!-- Additional Info Section -->
        <div class="grid grid-cols-4 md:grid-cols-4 gap-6">
            <!-- Nomor SEP -->
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-amber-200 mb-2">Nomor SEP:</label>
                <input type="text" wire:model="no_sep" placeholder="Nomor SEP"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                @error('no_sep') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Jenis Rawatan -->
            <div>
                <label class="block text-sm font-semibold text-amber-200 mb-2">Jenis Rawatan:</label>
                <select wire:model="jenis_rawatan"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                    <option value="RAWAT JALAN">Rawat Jalan</option>
                    <option value="RAWAT INAP">Rawat Inap</option>
                </select>
            </div>

            <!-- Tanggal Dokumen -->
            <div>
                <label class="block text-sm font-semibold text-amber-200 mb-2">Tanggal:</label>
                <input type="date" wire:model="tanggal_rawatan"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
            </div>
        </div>

        <!-- File Upload Section -->
        <div>
            <label class="block text-sm font-semibold text-amber-200 mb-2">Upload Scanned Documents:</label>
            <input type="file" wire:model="scanned_docs" multiple accept=".pdf,.jpg,.png"
                class="w-full bg-gray-700 text-gray-300 border border-gray-600 rounded-lg file:px-4 file:py-2 file:bg-amber-500 file:text-white file:border-0 focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
            @error('scanned_docs.*') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- File List with Reordering -->
        @if($scanned_docs)
        <div class="mt-4 space-y-2">
            @foreach($scanned_docs as $index => $doc)
            <div class="flex items-center gap-4 p-4 bg-gray-700 border border-gray-600 rounded-lg">
                <span class="text-amber-300 font-semibold">Page {{ $loop->iteration }}</span>

                <!-- Reorder Buttons -->
                <button type="button" wire:click.prevent="moveUp({{ $index }})"
                    class="px-3 py-1 text-xs bg-amber-500 text-white rounded-lg transition-all duration-200 ease-in-out shadow-md hover:bg-amber-400 hover:shadow-amber-500/30 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    ↑
                </button>
                <button type="button" wire:click.prevent="moveDown({{ $index }})"
                    class="px-3 py-1 text-xs bg-amber-500 text-white rounded-lg transition-all duration-200 ease-in-out shadow-md hover:bg-amber-400 hover:shadow-amber-500/30 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    ↓
                </button>

                <!-- File Info -->
                <span class="flex-1 text-gray-300 truncate">
                    {{ $doc->getClientOriginalName() }}
                </span>

                <!-- Preview Button -->
                <button type="button" wire:click.prevent="previewFile({{ $index }})"
                    class="px-4 py-1 bg-blue-600 text-white rounded-lg transition-all duration-200 ease-in-out shadow-md hover:bg-blue-500 hover:shadow-blue-500/30 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Preview
                </button>
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
                    <h3 class="text-xl font-semibold text-amber-300">PDF Preview</h3>
                    <button wire:click="closePreviewModal"
                        class="text-amber-300 hover:text-white text-2xl leading-none focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                        &times;
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="flex-1 min-h-0 relative">
                    @if($this->currentPreviewUrl)
                    <iframe src="{{ $this->currentPreviewUrl }}" 
                            class="w-full h-full border-0" 
                            frameborder="0"
                            style="min-height: 80vh;">
                    </iframe>
                    @endif
                </div>

                {{-- <!-- Close Button (Bottom) -->
                <div class="flex-shrink-0 px-6 py-6 border-t border-gray-600 flex justify-end">
                    <button wire:click.prevent="closePreviewModal"
                        class="px-6 py-2 bg-amber-500 text-white rounded-lg shadow-md transition-all duration-200 ease-in-out hover:bg-amber-400 hover:shadow-amber-500/30 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        Close
                    </button>
                </div> --}}
            </div>
        </div>
        @endif



        <!-- Submit Button -->
        <div class="text-center">
            <!-- Submit Button -->
            <button type="submit"
                class="w-full md:w-auto active:scale-95 px-8 py-3 bg-amber-500 text-white font-semibold rounded-lg transition-all duration-300 ease-in-out shadow-md hover:bg-amber-400 hover:shadow-amber-500/30 focus:outline-none focus:ring-4 focus:ring-amber-300">
                <span wire:loading.remove>Submit</span>
                <span wire:loading>Processing...</span>
            </button>
        </div>
    </form>

    <!-- Success Message -->
    @if (session('message'))
    <div class="mt-6 bg-green-500 text-white px-4 py-2 rounded-lg text-center">
        {{ session('message') }}
    </div>
    @endif
</div>