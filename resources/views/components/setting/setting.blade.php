<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Pengaturan</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-success border border-success text-success-fg px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-surface rounded-lg shadow-sm p-6 max-w-2xl">
        <form wire:submit="simpan">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-primary">Nama Perpustakaan <span class="text-danger-fg">*</span></label>
                    <input type="text" wire:model="namaPerpus" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    @error('namaPerpus') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-primary">Logo</label>
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="h-16 mb-2" alt="Preview logo">
                    @elseif ($logoPath)
                        <img src="{{ str_starts_with($logoPath, 'upload/') ? asset($logoPath) : asset('storage/'.$logoPath) }}" class="h-16 mb-2" alt="Logo">
                    @endif
                    <input type="file" wire:model="logo" class="mt-1 block w-full text-sm text-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-accent-soft file:text-accent-fg hover:file:bg-accent-soft">
                    @error('logo') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Lama Pinjam (hari) <span class="text-danger-fg">*</span></label>
                        <input type="number" wire:model="lamaPinjam" min="1" max="365" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('lamaPinjam') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Maksimal Buku <span class="text-danger-fg">*</span></label>
                        <input type="number" wire:model="maksimalBuku" min="1" max="20" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('maksimalBuku') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="border-t border-border pt-4 mt-4">
                    <h3 class="font-semibold text-text-primary mb-3">Tanda Tangan Bukti Peminjaman</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Nama Petugas Penanda Tangan <span class="text-danger-fg">*</span></label>
                            <input type="text" wire:model="namaPetugas" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                            @error('namaPetugas') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Jabatan Penanda Tangan <span class="text-danger-fg">*</span></label>
                            <input type="text" wire:model="jabatanPetugas" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                            @error('jabatanPetugas') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-accent hover:bg-accent-hover text-accent-fg px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>