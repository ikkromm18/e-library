<div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $totalEksemplar }}</div>
            <div class="text-sm text-gray-500">Jumlah Buku</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $totalJudul }}</div>
            <div class="text-sm text-gray-500">Jumlah Judul</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $totalAnggota }}</div>
            <div class="text-sm text-gray-500">Jumlah Anggota</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $sedangDipinjam }}</div>
            <div class="text-sm text-gray-500">Sedang Dipinjam</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-red-600">{{ $terlambat }}</div>
            <div class="text-sm text-gray-500">Terlambat</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $kembaliHariIni }}</div>
            <div class="text-sm text-gray-500">Kembali Hari Ini</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-4">Peminjaman 7 Hari Terakhir</h2>
        <div class="flex items-end gap-2 h-40">
            @foreach ($grafik as $tanggal => $jumlah)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="text-xs text-gray-500">{{ $jumlah }}</div>
                    <div class="w-full bg-blue-500 rounded-t" style="height: {{ $jumlah > 0 ? ($jumlah / $maksGrafik) * 100 : 4 }}%"></div>
                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($tanggal)->format('d/m') }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('buku.index') }}" class="bg-white rounded-lg shadow p-4 text-center text-blue-600 hover:bg-blue-50 font-medium">Tambah Buku</a>
        <a href="{{ route('peminjaman.index') }}" class="bg-white rounded-lg shadow p-4 text-center text-blue-600 hover:bg-blue-50 font-medium">Pinjam Buku</a>
        <a href="{{ route('pengembalian.index') }}" class="bg-white rounded-lg shadow p-4 text-center text-blue-600 hover:bg-blue-50 font-medium">Kembalikan Buku</a>
    </div>
</div>