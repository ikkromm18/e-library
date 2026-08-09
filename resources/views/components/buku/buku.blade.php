<div wire:preserveScroll>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Master Buku</h1>
        <div class="flex gap-2">
            <a href="{{ route('buku.import') }}" class="bg-surface-muted hover:bg-surface-raised text-text-primary px-4 py-2 rounded-lg text-sm font-medium">Import Excel</a>
            <button wire:click="create" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-sm font-medium">Tambah Buku</button>
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
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Cari kode/ISBN/judul/pengarang..." class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
            </div>
            <div>
                <select wire:model.live="filterKategori" class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
                    <option value="">Semua Kategori</option>
                    @forelse (($kategoriList ?? []) as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div>
                <select wire:model.live="filterRak" class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
                    <option value="">Semua Rak</option>
                    @forelse (($rakList ?? []) as $r)
                        <option value="{{ $r->id }}">{{ $r->kode }} - {{ $r->nama }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div>
                <select wire:model.live="filterPengarang" class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
                    <option value="">Semua Pengarang</option>
                    @foreach (($pengarangList ?? []) as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="filterPenerbit" class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
                    <option value="">Semua Penerbit</option>
                    @foreach (($penerbitList ?? []) as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="filterTahun" class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
                    <option value="">Semua Tahun</option>
                    @foreach (($tahunList ?? []) as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
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
    @if ($showDetail && $detailBuku)
        <div x-data="{ open: true }" x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$set('showDetail', false)">
            <div class="bg-surface-raised rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-text-primary">Detail Buku</h2>
                    <button wire:click="$set('showDetail', false)" class="text-text-secondary hover:text-text-primary text-xl leading-none">&times;</button>
                </div>
                @if ($detailBuku->cover)
                    <img src="{{ asset('storage/'.$detailBuku->cover) }}" class="h-40 object-cover rounded-lg mb-4" alt="Cover {{ $detailBuku->judul }}">
                @endif
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="font-medium text-text-secondary">Kode:</span> {{ $detailBuku->kode }}</div>
                    <div><span class="font-medium text-text-secondary">ISBN:</span> {{ $detailBuku->isbn ?? '-' }}</div>
                    <div class="col-span-2"><span class="font-medium text-text-secondary">Judul:</span> {{ $detailBuku->judul }}</div>
                    @if ($detailBuku->sub_judul)
                        <div class="col-span-2"><span class="font-medium text-text-secondary">Sub Judul:</span> {{ $detailBuku->sub_judul }}</div>
                    @endif
                    <div><span class="font-medium text-text-secondary">Kategori:</span> {{ $detailBuku->kategori->nama }}</div>
                    <div><span class="font-medium text-text-secondary">Rak:</span> {{ $detailBuku->rak->kode }}</div>
                    <div><span class="font-medium text-text-secondary">Pengarang:</span> {{ $detailBuku->pengarang ?? '-' }}</div>
                    <div><span class="font-medium text-text-secondary">Penerbit:</span> {{ $detailBuku->penerbit ?? '-' }}</div>
                    <div><span class="font-medium text-text-secondary">Tahun:</span> {{ $detailBuku->tahun ?? '-' }}</div>
                    <div><span class="font-medium text-text-secondary">Bahasa:</span> {{ $detailBuku->bahasa ?? '-' }}</div>
                    <div><span class="font-medium text-text-secondary">Eksemplar:</span> {{ $detailBuku->jumlah_eksemplar }}</div>
                    <div><span class="font-medium text-text-secondary">Stok Tersedia:</span> {{ $detailBuku->stokTersedia() }}</div>
                    <div><span class="font-medium text-text-secondary">Status:</span>
                        <span class="px-2 py-1 rounded-full text-xs {{ $detailBuku->status === 'aktif' ? 'bg-success text-success-fg' : 'bg-danger text-danger-fg' }}">
                            {{ $detailBuku->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                    @if ($detailBuku->deskripsi)
                        <div class="col-span-2"><span class="font-medium text-text-secondary">Deskripsi:</span> {{ $detailBuku->deskripsi }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Form --}}
    @if ($showForm)
        <div class="bg-surface rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4 text-text-primary">{{ $editId ? 'Edit Buku' : 'Tambah Buku' }}</h2>
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Kode Buku</label>
                        <input type="text" wire:model="kode" class="mt-1 block w-full rounded-md border-border shadow-sm bg-surface-muted" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">ISBN</label>
                        <input type="text" wire:model="isbn" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('isbn') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-primary">Judul <span class="text-danger-fg">*</span></label>
                        <input type="text" wire:model="judul" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                        @error('judul') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-primary">Sub Judul</label>
                        <input type="text" wire:model="sub_judul" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Kategori <span class="text-danger-fg">*</span></label>
                        <select wire:model="kategori_id" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                            <option value="">Pilih Kategori</option>
                             @forelse (($kategoriList ?? []) as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @empty
                            @endforelse
                        </select>
                        @error('kategori_id') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Rak <span class="text-danger-fg">*</span></label>
                        <select wire:model="rak_id" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                            <option value="">Pilih Rak</option>
                             @forelse (($rakList ?? []) as $r)
                                <option value="{{ $r->id }}">{{ $r->kode }} - {{ $r->nama }}</option>
                            @empty
                            @endforelse
                        </select>
                        @error('rak_id') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Pengarang</label>
                        <input type="text" wire:model="pengarang" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Penerbit</label>
                        <input type="text" wire:model="penerbit" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Tahun</label>
                        <input type="number" wire:model="tahun" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent" min="1900" max="2099">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Bahasa</label>
                        <input type="text" wire:model="bahasa" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Jumlah Eksemplar <span class="text-danger-fg">*</span></label>
                        <input type="number" wire:model="jumlah_eksemplar" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent" min="1">
                        @error('jumlah_eksemplar') <span class="text-danger-fg text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Status <span class="text-danger-fg">*</span></label>
                        <select wire:model="status" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
                            <option value="aktif">Aktif</option>
                            <option value="tidak">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-primary">Deskripsi</label>
                        <textarea wire:model="deskripsi" rows="3" class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" wire:loading.attr="disabled" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">Simpan</button>
                    <button type="button" wire:click="cancel" wire:loading.attr="disabled" class="bg-surface-muted hover:bg-surface-raised text-text-primary px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">Batal</button>
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Judul</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Rak</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Stok</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                 @forelse (($bukuList ?? []) as $buku)
                    <tr>
                        <td class="px-4 py-3 text-sm text-text-secondary">{{ $bukuList->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 text-sm font-mono">
                            <div class="flex items-center gap-2">
                                @if ($buku->cover)
                                    <img src="{{ asset('storage/'.$buku->cover) }}" class="h-10 w-8 object-cover rounded" alt="Cover">
                                @endif
                                <span>{{ $buku->kode }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-text-primary">{{ $buku->judul }}</td>
                        <td class="px-4 py-3 text-sm text-text-secondary">{{ $buku->kategori->nama }}</td>
                        <td class="px-4 py-3 text-sm text-text-secondary">{{ $buku->rak->kode }}</td>
                        <td class="px-4 py-3 text-sm text-text-secondary">{{ ($buku->jumlah_eksemplar - (int) $buku->stok_dipakai) }} / {{ $buku->jumlah_eksemplar }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs {{ $buku->status === 'aktif' ? 'bg-success text-success-fg' : 'bg-danger text-danger-fg' }}">
                                {{ $buku->status === 'aktif' ? 'Aktif' : 'Tidak' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm space-x-1">
                            <button wire:click="detail({{ $buku->id }})" wire:loading.attr="disabled" class="text-accent hover:text-text-primary disabled:opacity-50">Detail</button>
                            <button wire:click="edit({{ $buku->id }})" wire:loading.attr="disabled" class="text-accent hover:text-accent-hover disabled:opacity-50">Edit</button>
                            <button wire:click="delete({{ $buku->id }})" wire:loading.attr="disabled" wire:confirm="Yakin hapus buku ini?" class="text-danger-fg hover:text-danger-fg disabled:opacity-50">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-4 text-center text-sm text-text-secondary">Tidak ada buku ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-border">
            @if ($bukuList)
                {{ $bukuList->links() }}
            @endif
        </div>
    </div>
</div>
