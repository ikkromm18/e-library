<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Import Anggota</h1>
        <p class="text-sm text-gray-500 mt-1">Upload file Excel (.xlsx) untuk import data anggota secara massal.</p>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Upload File</h2>
        <form wire:submit="preview">
            <div>
                <label class="block text-sm font-medium text-gray-700">File Excel</label>
                <input type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Preview</button>
                <a href="{{ route('anggota.import.template') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Download Template</a>
            </div>
        </form>
    </div>

    @if ($showPreview && $importResult)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Hasil Preview</h2>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $importResult['imported'] }}</div>
                    <div class="text-sm text-gray-500">Akan Diimport</div>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $importResult['skipped'] }}</div>
                    <div class="text-sm text-gray-500">Dilewati (Duplikat)</div>
                </div>
                <div class="bg-red-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ count($importResult['errors']) }}</div>
                    <div class="text-sm text-gray-500">Error</div>
                </div>
            </div>

            @if (count($importResult['errors']) > 0)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-red-700 mb-2">Error Details:</h3>
                    <ul class="list-disc list-inside text-sm text-red-600">
                        @foreach ($importResult['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($importResult['imported'] > 0)
                <button wire:click="confirmImport" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Konfirmasi Import ({{ $importResult['imported'] }} data)
                </button>
            @endif
        </div>
    @endif
</div>
