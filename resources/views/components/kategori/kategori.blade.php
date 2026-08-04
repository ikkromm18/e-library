<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Kategori Buku</h1>
        <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Tambah Kategori
        </button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    @if ($showForm)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">{{ $editId ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
            <form wire:submit="save">
                <div class="max-w-md">
                    <label class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                    <input type="text" wire:model="nama" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('nama') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                    <button type="button" wire:click="cancel" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Batal</button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Buku</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($kategoriList as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->nama }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $item->bukus_count }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button wire:click="edit({{ $item->id }})" class="text-blue-600 hover:text-blue-900">Edit</button>
                            @if ($item->bukus_count === 0)
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin hapus kategori ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
