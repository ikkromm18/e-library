<div wire:preserveScroll>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Rak Buku</h1>
        <button wire:click="create" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-sm font-medium">Tambah Rak</button>
    </div>

    @if (session()->has('success'))
        <div class="bg-success border border-success text-success-fg px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-danger border border-danger-fg text-danger-fg px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    @if ($showForm)
        <div class="bg-surface rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-text-primary mb-4">{{ $editId ? 'Edit Rak' : 'Tambah Rak' }}</h2>
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Kode Rak</label>
                        <input type="text" wire:model="kode" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent" placeholder="A-01">
                        @error('kode') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Nama Rak</label>
                        <input type="text" wire:model="nama" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('nama') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Keterangan</label>
                        <input type="text" wire:model="keterangan" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                    <button type="button" wire:click="cancel" class="bg-surface-muted hover:bg-surface-raised text-text-primary px-4 py-2 rounded-lg text-sm font-medium">Batal</button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-surface border border-border rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Keterangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Jumlah Buku</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-surface divide-y divide-border">
                @forelse ($rakList as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-text-primary">{{ $rakList->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-4 text-sm font-mono font-medium text-text-primary">{{ $item->kode }}</td>
                        <td class="px-6 py-4 text-sm text-text-primary">{{ $item->nama }}</td>
                        <td class="px-6 py-4 text-sm text-text-secondary">{{ $item->keterangan ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-text-secondary">{{ $item->bukus_count }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button wire:click="edit({{ $item->id }})" class="text-accent hover:text-accent-hover">Edit</button>
                            @if ($item->bukus_count === 0)
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin hapus rak ini?" class="text-danger-fg hover:text-danger-fg">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-text-secondary">Belum ada rak.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t border-border flex items-center justify-between">
            <select wire:model.live="perPage" class="rounded-md border-border shadow-sm text-sm">
                @foreach ([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}">{{ $n }}</option>
                @endforeach
            </select>
            {{ $rakList->links() }}
        </div>
    </div>
</div>
