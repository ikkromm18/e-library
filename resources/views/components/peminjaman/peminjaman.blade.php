<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Peminjaman Buku</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-2">1. Cari Anggota</h2>
        @if ($anggotaDipilih)
            <div class="flex items-center justify-between bg-green-50 border border-green-300 rounded p-3">
                <div>
                    <div class="font-medium">{{ $anggotaDipilih->nama }}</div>
                    <div class="text-sm text-gray-500">NIS: {{ $anggotaDipilih->nis }} &middot; Kelas: {{ $anggotaDipilih->kelas }} &middot; {{ $anggotaDipilih->status }}</div>
                </div>
                <button wire:click="gantiAnggota" class="text-sm text-red-600 hover:text-red-900">Ganti</button>
            </div>
        @else
            <input type="text" wire:model.live="searchAnggota" placeholder="Cari nama / NIS anggota..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @if ($searchAnggota)
                <ul class="mt-2 divide-y divide-gray-100">
                    @forelse ($hasilAnggota as $a)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $a->nama }} <span class="text-sm text-gray-500">({{ $a->nis }} &mdash; {{ $a->kelas }})</span></span>
                            <button wire:click="pilihAnggota({{ $a->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Pilih</button>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Tidak ditemukan.</li>
                    @endforelse
                </ul>
            @endif
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-2">2. Pilih Buku (maksimal {{ $maksimalBuku }})</h2>
        <input type="text" wire:model.live="searchBuku" placeholder="Cari kode / ISBN / judul..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        @if ($searchBuku)
            <ul class="mt-2 divide-y divide-gray-100">
                @forelse ($hasilBuku as $b)
                    <li class="flex items-center justify-between py-2">
                        <div>
                            <div class="text-sm font-medium">{{ $b->judul }}</div>
                            <div class="text-sm text-gray-500">{{ $b->kode }} &mdash; stok {{ $b->stokTersedia() }}/{{ $b->jumlah_eksemplar }}</div>
                        </div>
                        <button wire:click="tambahBuku({{ $b->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Tambah</button>
                    </li>
                @empty
                    <li class="py-2 text-sm text-gray-500">Tidak ditemukan.</li>
                @endforelse
            </ul>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-2">3. Daftar Buku</h2>
        @if (count($cart) === 0)
            <p class="text-sm text-gray-500">Belum ada buku dipilih.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200 mb-4">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($cart as $index => $item)
                        <tr>
                            <td class="px-4 py-2 text-sm font-mono">{{ $item['kode'] }}</td>
                            <td class="px-4 py-2 text-sm">{{ $item['judul'] }}</td>
                            <td class="px-4 py-2 text-right">
                                <button wire:click="hapusDariCart({{ $index }})" class="text-red-600 hover:text-red-900 text-sm">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button wire:click="simpan" @if (! $anggotaDipilih) disabled @endif class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Simpan Peminjaman</button>
        @endif
    </div>
</div>
