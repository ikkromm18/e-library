<div>
    <div class="bg-surface rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-text-primary">Upload File</h3>
            <a href="{{ route('buku.import.template') }}" class="text-accent hover:text-accent-hover text-sm font-medium">Download Template</a>
        </div>

        <div class="mb-4">
            <input type="file" wire:model="file" class="block w-full text-sm text-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-accent-soft file:text-accent-fg hover:file:bg-accent-soft">
            @error('file') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex gap-2">
            <button wire:click="preview" @if (!$file) disabled @endif class="bg-surface-muted text-text-primary px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50">Preview</button>
            <button wire:click="confirmImport" @if (!$showPreview) disabled @endif class="bg-accent text-accent-fg px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50">Import</button>
        </div>

        @if ($importResult)
            <div class="mt-4 p-4 bg-surface-muted rounded-lg">
                <div class="text-sm text-text-primary">
                    <span class="font-medium">Berhasil:</span> {{ $importResult['imported'] }} baris
                    <span class="ml-4 font-medium">Dilewati:</span> {{ $importResult['skipped'] }} baris
                </div>
                @if (!empty($importResult['errors']))
                    <div class="mt-2 text-sm text-danger-fg">
                        <span class="font-medium">Error:</span>
                        <ul class="list-disc list-inside">
                            @foreach ($importResult['errors'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
