<div wire:preserveScroll>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Master Anggota</h1>
        <div class="flex gap-2">
            <a href="{{ route('anggota.import') }}" class="bg-surface-muted hover:bg-surface-raised text-text-primary px-4 py-2 rounded-lg text-sm font-medium">Import Excel</a>
            <button wire:click="create" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-sm font-medium">Tambah Anggota</button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="bg-success border border-success text-success-fg px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-danger border border-danger-fg text-danger-fg px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    {{-- Search & Filter --}}
    <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Cari NIS/nama/kelas..." class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
            </div>
            <div>
                <select wire:model.live="filterStatus" class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="lulus">Lulus</option>
                    <option value="pindah">Pindah</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-end gap-2">
            <span class="text-sm text-text-secondary">Baris per halaman:</span>
            <select wire:model.live="perPage" class="rounded-md border-border shadow-sm text-sm">
                @foreach ([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}">{{ $n }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Detail Modal --}}
    @if ($showDetail && $detailAnggota)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$set('showDetail', false)">
            <div class="bg-surface-raised rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-text-primary">Detail Anggota</h2>
                    <button wire:click="$set('showDetail', false)" class="text-text-secondary hover:text-text-primary">&times;</button>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="font-medium text-text-secondary">NIS:</span> {{ $detailAnggota->nis }}</div>
                    <div><span class="font-medium text-text-secondary">Nama:</span> {{ $detailAnggota->nama }}</div>
                    <div><span class="font-medium text-text-secondary">Jenis Kelamin:</span> {{ $detailAnggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                    <div><span class="font-medium text-text-secondary">Kelas:</span> {{ $detailAnggota->kelas ?? '-' }}</div>
                    <div class="col-span-2"><span class="font-medium text-text-secondary">Alamat:</span> {{ $detailAnggota->alamat ?? '-' }}</div>
                    <div><span class="font-medium text-text-secondary">No HP:</span> {{ $detailAnggota->no_hp ?? '-' }}</div>
                    <div><span class="font-medium text-text-secondary">Tanggal Masuk:</span> {{ $detailAnggota->tanggal_masuk->format('d/m/Y') }}</div>
                    <div><span class="font-medium text-text-secondary">Status:</span>
                        <span class="px-2 py-1 rounded-full text-xs {{ $detailAnggota->status === 'aktif' ? 'bg-success text-success-fg' : 'bg-danger text-danger-fg' }}">
                            {{ ucfirst($detailAnggota->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Form --}}
    @if ($showForm)
        <div class="bg-surface rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4 text-text-primary">{{ $editId ? 'Edit Anggota' : 'Tambah Anggota' }}</h2>
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-primary">NIS <span class="text-danger-fg">*</span></label>
                        <input type="text" wire:model="nis" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent" {{ $editId ? 'readonly' : '' }}>
                        @error('nis') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Nama <span class="text-danger-fg">*</span></label>
                        <input type="text" wire:model="nama" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('nama') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Jenis Kelamin <span class="text-danger-fg">*</span></label>
                        <select wire:model="jenis_kelamin" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        @error('jenis_kelamin') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Kelas</label>
                        <input type="text" wire:model="kelas" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-primary">Alamat</label>
                        <textarea wire:model="alamat" rows="2" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">No HP</label>
                        <input type="text" wire:model="no_hp" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Tanggal Masuk <span class="text-danger-fg">*</span></label>
                        <input type="date" wire:model="tanggal_masuk" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('tanggal_masuk') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Status <span class="text-danger-fg">*</span></label>
                        <select wire:model="status" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                            <option value="aktif">Aktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="pindah">Pindah</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                        @error('status') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                    <button type="button" wire:click="cancel" class="bg-surface-muted hover:bg-surface-raised text-text-primary px-4 py-2 rounded-lg text-sm font-medium">Batal</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-surface border border-border shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">NIS</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">JK</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Kelas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                 @forelse (($anggotaList ?? []) as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm text-text-secondary">{{ $anggotaList->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $item->nis }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-text-primary">{{ $item->nama }}</td>
                        <td class="px-4 py-3 text-sm text-text-secondary">{{ $item->jenis_kelamin }}</td>
                        <td class="px-4 py-3 text-sm text-text-secondary">{{ $item->kelas ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs {{ $item->status === 'aktif' ? 'bg-success text-success-fg' : 'bg-danger text-danger-fg' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm space-x-1">
                            <button wire:click="detail({{ $item->id }})" class="text-success hover:text-success-fg">Detail</button>
                            <button wire:click="edit({{ $item->id }})" class="text-accent hover:text-accent-hover">Edit</button>
                            <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin hapus anggota ini?" class="text-danger-fg hover:text-danger-fg">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-4 text-center text-sm text-text-secondary">Tidak ada anggota ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-border">
            @if ($anggotaList)
                {{ $anggotaList->links() }}
            @endif
        </div>
    </div>
</div>