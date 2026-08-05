<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pengembalian Buku</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if ($peminjaman)
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="text-lg font-semibold">{{ $peminjaman->no_transaksi }}</div>
                    <div class="text-sm text-gray-500">
                        {{ $peminjaman->anggota->nama }} ({{ $peminjaman->anggota->nis }}) &middot;
                        Petugas: {{ $peminjaman->petugas->name }} &middot;
                        Pinjam: {{ $peminjaman->tanggal->format('d/m/Y') }} &middot;
                        Jatuh Tempo: {{ $peminjaman->tanggal_jatuh_tempo->format('d/m/Y') }}
                    </div>
                </div>
                <button wire:click="cariLagi" class="text-sm text-red-600 hover:text-red-900">Cari Lain</button>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Buku</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($details as $detail)
                        <tr>
                            <td class="px-4 py-2 text-sm">{{ $detail->buku->judul }}</td>
                            <td class="px-4 py-2 text-sm">
                                @if ($detail->tanggal_kembali)
                                    @if ($detail->keterlambatan_hari > 0)
                                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Terlambat {{ $detail->keterlambatan_hari }} Hari</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Tepat Waktu</span>
                                    @endif
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">Belum Kembali</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                @if (! $detail->tanggal_kembali)
                                    <button wire:click="kembalikan({{ $detail->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Kembalikan</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <input type="text" wire:model.live="search" placeholder="Cari no transaksi / nama / NIS anggota..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @if ($search)
                <ul class="mt-2 divide-y divide-gray-100">
                    @forelse ($hasilTransaksi as $t)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $t->no_transaksi }} <span class="text-sm text-gray-500">&mdash; {{ $t->anggota->nama }}</span></span>
                            <button wire:click="pilih({{ $t->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Pilih</button>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Tidak ditemukan transaksi aktif.</li>
                    @endforelse
                </ul>
            @endif
        </div>
    @endif
</div>
