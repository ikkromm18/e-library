<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Master Buku</h1>
        <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Tambah Buku</button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    {{-- Search & Filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Cari kode/ISBN/judul/pengarang..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <select wire:model.live="filterKategori" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Semua Kategori</option>
                    @forelse (($kategoriList ?? []) as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div>
                <select wire:model.live="filterRak" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Semua Rak</option>
                    @forelse (($rakList ?? []) as $r)
                        <option value="{{ $r->id }}">{{ $r->kode }} - {{ $r->nama }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    @if ($showDetail && $detailBuku)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="$set('showDetail', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Detail Buku</h2>
                    <button wire:click="$set('showDetail', false)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="font-medium text-gray-500">Kode:</span> {{ $detailBuku->kode }}</div>
                    <div><span class="font-medium text-gray-500">ISBN:</span> {{ $detailBuku->isbn ?? '-' }}</div>
                    <div class="col-span-2"><span class="font-medium text-gray-500">Judul:</span> {{ $detailBuku->judul }}</div>
                    @if ($detailBuku->sub_judul)
                        <div class="col-span-2"><span class="font-medium text-gray-500">Sub Judul:</span> {{ $detailBuku->sub_judul }}</div>
                    @endif
                    <div><span class="font-medium text-gray-500">Kategori:</span> {{ $detailBuku->kategori->nama }}</div>
                    <div><span class="font-medium text-gray-500">Rak:</span> {{ $detailBuku->rak->kode }}</div>
                    <div><span class="font-medium text-gray-500">Pengarang:</span> {{ $detailBuku->pengarang ?? '-' }}</div>
                    <div><span class="font-medium text-gray-500">Penerbit:</span> {{ $detailBuku->penerbit ?? '-' }}</div>
                    <div><span class="font-medium text-gray-500">Tahun:</span> {{ $detailBuku->tahun ?? '-' }}</div>
                    <div><span class="font-medium text-gray-500">Bahasa:</span> {{ $detailBuku->bahasa ?? '-' }}</div>
                    <div><span class="font-medium text-gray-500">Eksemplar:</span> {{ $detailBuku->jumlah_eksemplar }}</div>
                    <div><span class="font-medium text-gray-500">Stok Tersedia:</span> {{ $detailBuku->stokTersedia() }}</div>
                    <div><span class="font-medium text-gray-500">Status:</span>
                        <span class="px-2 py-1 rounded-full text-xs {{ $detailBuku->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $detailBuku->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                    @if ($detailBuku->deskripsi)
                        <div class="col-span-2"><span class="font-medium text-gray-500">Deskripsi:</span> {{ $detailBuku->deskripsi }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Form --}}
    @if ($showForm)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">{{ $editId ? 'Edit Buku' : 'Tambah Buku' }}</h2>
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode Buku</label>
                        <input type="text" wire:model="kode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ISBN</label>
                        <input type="text" wire:model="isbn" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('isbn') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Judul <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="judul" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('judul') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Sub Judul</label>
                        <input type="text" wire:model="sub_judul" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                        <select wire:model="kategori_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                             @forelse (($kategoriList ?? []) as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @empty
                            @endforelse
                        </select>
                        @error('kategori_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rak <span class="text-red-500">*</span></label>
                        <select wire:model="rak_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Rak</option>
                             @forelse (($rakList ?? []) as $r)
                                <option value="{{ $r->id }}">{{ $r->kode }} - {{ $r->nama }}</option>
                            @empty
                            @endforelse
                        </select>
                        @error('rak_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pengarang</label>
                        <input type="text" wire:model="pengarang" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Penerbit</label>
                        <input type="text" wire:model="penerbit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <input type="number" wire:model="tahun" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="1900" max="2099">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bahasa</label>
                        <input type="text" wire:model="bahasa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jumlah Eksemplar <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="jumlah_eksemplar" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="1">
                        @error('jumlah_eksemplar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select wire:model="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="aktif">Aktif</option>
                            <option value="tidak">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Cover</label>
                        <input type="file" wire:model="cover" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea wire:model="deskripsi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                    <button type="button" wire:click="cancel" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Batal</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rak</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                 @forelse (($bukuList ?? []) as $buku)
                    <tr>
                        <td class="px-4 py-3 text-sm font-mono">{{ $buku->kode }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $buku->judul }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $buku->kategori->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $buku->rak->kode }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $buku->stokTersedia() }} / {{ $buku->jumlah_eksemplar }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs {{ $buku->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $buku->status === 'aktif' ? 'Aktif' : 'Tidak' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm space-x-1">
                            <button wire:click="detail({{ $buku->id }})" class="text-green-600 hover:text-green-900">Detail</button>
                            <button wire:click="edit({{ $buku->id }})" class="text-blue-600 hover:text-blue-900">Edit</button>
                            <button wire:click="delete({{ $buku->id }})" wire:confirm="Yakin hapus buku ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">Tidak ada buku ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">
            @if ($bukuList)
                {{ $bukuList->links() }}
            @endif
        </div>
    </div>
</div>
