<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex gap-2 mb-4">
            <button wire:click="$set('tipe', 'buku')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'buku' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">Buku</button>
            <button wire:click="$set('tipe', 'anggota')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'anggota' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">Anggota</button>
            <button wire:click="$set('tipe', 'transaksi')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'transaksi' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">Transaksi</button>
        </div>

        <div class="flex flex-wrap gap-4 mb-4">
            @if ($tipe === 'buku')
                <select wire:model.live="kategoriId" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach
                </select>
                <select wire:model.live="rakId" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Semua Rak</option>
                    @foreach ($rakList as $r)<option value="{{ $r->id }}">{{ $r->kode }}</option>@endforeach
                </select>
            @elseif ($tipe === 'anggota')
                <select wire:model.live="statusAnggota" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Semua Anggota</option>
                    <option value="aktif">Aktif</option>
                    <option value="lulus">Lulus</option>
                    <option value="pindah">Pindah</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            @else
                <select wire:model.live="jenisTransaksi" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="peminjaman">Peminjaman</option>
                    <option value="pengembalian">Pengembalian</option>
                    <option value="terlambat">Terlambat</option>
                </select>
            @endif
        </div>

        <div class="flex gap-2 mb-4">
            <a href="{{ route('laporan.export', ['tipe' => $tipe, 'format' => 'pdf', 'kategori_id' => $kategoriId, 'rak_id' => $rakId, 'status' => $statusAnggota, 'jenis' => $jenisTransaksi]) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Export PDF</a>
            <a href="{{ route('laporan.export', ['tipe' => $tipe, 'format' => 'excel', 'kategori_id' => $kategoriId, 'rak_id' => $rakId, 'status' => $statusAnggota, 'jenis' => $jenisTransaksi]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Export Excel</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach ($headings as $h)
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($rows as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="20" class="px-4 py-4 text-center text-sm text-gray-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>