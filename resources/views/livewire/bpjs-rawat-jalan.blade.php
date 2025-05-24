<div>
    <div>
        <flux:input type="file" label="Upload Scanned Documents" wire:model="scanned_docs" multiple
            accept=".pdf,.jpg,.png" placeholder="Upload Scanned Documents" />
    </div>
    @if ($scanned_docs)
    <div class="mt-2">
        <h3>Uploaded Files:</h3>
        <ul>
            @foreach ($scanned_docs as $file)
            <li>{{ $file->getClientOriginalName() }}</li>
            @endforeach
        </ul>
        {{ $scanned_docs_count }} file(s) selected.
    </div>
    <div wire:loading>Memproses PDF...</div>
    {{-- <div class="mt-2">
        Hasil Ekstraksi: {{ $pdfText }}
    </div> --}}
    <div class="mt-2">
        <div class="space-y-4">
            @if($no_sep)
            <div><span class="font-semibold">No. SEP:</span> {{ $no_sep }}</div>
            <div><span class="font-semibold">Tanggal SEP:</span> {{ $tgl_sep }}</div>
            <div><span class="font-semibold">No. Kartu:</span> {{ $no_kartu }}</div>
            <div><span class="font-semibold">No. MR:</span> {{ $no_mr }}</div>
            <div><span class="font-semibold">Nama Peserta:</span> {{ $nama }}</div>
            @endif

            @error('extraction')
            <div class="text-red-500">{{ $message }}</div>
            @enderror
        </div>
    </div>
    @endif
</div>