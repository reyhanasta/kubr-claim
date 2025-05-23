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

    @endif
</div>