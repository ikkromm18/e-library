<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Import Anggota</h1>
        <p class="text-sm text-text-secondary mt-1">Upload file Excel (.xlsx) untuk import data anggota secara massal.</p>
    </div>

    @if (session()->has('success'))
        <div class="bg-success border border-success text-success-fg px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-danger border border-danger-fg text-danger-fg px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-surface rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4 text-text-primary">Upload File</h2>
        <form wire:submit="preview">
            <div>
                <label class="block text-sm font-medium text-text-primary">File Excel</label>
                <input type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="mt-1 block w-full text-sm text-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-accent-soft file:text-accent hover:file:bg-accent-hover">
                @error('file') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-sm font-medium">Preview</button>
                <a href="{{ route('anggota.import.template') }}" class="bg-surface-muted hover:bg-surface-raised text-text-primary px-4 py-2 rounded-lg text-sm font-medium">Download Template</a>
            </div>
        </form>
    </div>

    @if ($showPreview && $importResult)
        <div class="bg-surface rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4 text-text-primary">Hasil Preview</h2>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-success rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-success-fg">{{ $importResult['imported'] }}</div>
                    <div class="text-sm text-text-secondary">Akan Diimport</div>
                </div>
                <div class="bg-warning rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-warning-fg">{{ $importResult['skipped'] }}</div>
                    <div class="text-sm text-text-secondary">Dilewati (Duplikat)</div>
                </div>
                <div class="bg-danger rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-danger-fg">{{ count($importResult['errors']) }}</div>
                    <div class="text-sm text-text-secondary">Error</div>
                </div>
            </div>

            @if (count($importResult['errors']) > 0)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-danger-fg mb-2">Error Details:</h3>
                    <ul class="list-disc list-inside text-sm text-danger-fg">
                        @foreach ($importResult['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($importResult['imported'] > 0)
                <button wire:click="confirmImport" class="bg-success text-success-fg hover:bg-success-solid px-4 py-2 rounded-lg text-sm font-medium">
                    Konfirmasi Import ({{ $importResult['imported'] }} data)
                </button>
            @endif
        </div>
    @endif
</div>