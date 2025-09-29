<div>
    <div wire:offline>
        This device is currently offline.
    </div>
    <div>
        <!-- Header -->
        <div class="mb-3 text-center">
            <flux:heading size="xl" level="1" class="text-amber">BPJS Claim Submission</flux:heading>
            <flux:subheading size="md" class="text-gray-200">Please fill in the patient's details below.
            </flux:subheading>
        </div>
        <div class="" wire:show='showUploadedData' wire:transition.scale.origin.top.duration.300ms>
            <div class="max-w-4xl mx-auto p-2 rounded-xl shadow-lg">
                <!-- PDF Preview for 3-section format (similar to A5 proportions) -->
                <div class="w-full bg-white rounded-lg overflow-hidden shadow-lg relative" style="padding-top: 34%">
                    <iframe src="{{ $previewUrls[0] ?? '' }}#toolbar=0&navpanes=0&scrollbar=0"
                        class="absolute top-0 left-0 w-full h-full" frameborder="0">
                    </iframe>
                </div>
            </div>
            <div wire:submit.prevent="submit" wire:target="submit" class="space-y-4">
                <!-- Patient Info Section -->
                <div class="max-w-4xl mx-auto p-6 bg-gray-700 text-gray-100 rounded-xl shadow-lg">
                    <h1 class="text-2xl font-bold">
                        Data Pasien</h1>
                    <hr class="my-4">
                    <!-- SIMRS Data Display -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                    </div>
                </div>


            </div>
        </div>
        @if($showUploadedData == false)
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
    <div wire:loading.flex wire:target="cancelForm"
        class="fixed inset-0 z-100 bg-neutral-900/60 flex items-center justify-center backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 text-center animate-fade-in">
            <svg class="h-12 w-12 animate-spin text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <p class="text-red-100 font-semibold text-lg">Membatalkan dokumen...</p>
        </div>
    </div>
</div>