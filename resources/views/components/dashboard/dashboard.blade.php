<div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-surface rounded-lg shadow-sm p-4">
            <div class="text-3xl font-bold text-text-primary">{{ $totalEksemplar }}</div>
            <div class="text-sm text-text-secondary">Jumlah Buku</div>
        </div>
        <div class="bg-surface rounded-lg shadow-sm p-4">
            <div class="text-3xl font-bold text-text-primary">{{ $totalJudul }}</div>
            <div class="text-sm text-text-secondary">Jumlah Judul</div>
        </div>
        <div class="bg-surface rounded-lg shadow-sm p-4">
            <div class="text-3xl font-bold text-text-primary">{{ $totalAnggota }}</div>
            <div class="text-sm text-text-secondary">Jumlah Anggota</div>
        </div>
        <div class="bg-surface rounded-lg shadow-sm p-4">
            <div class="text-3xl font-bold text-text-primary">{{ $sedangDipinjam }}</div>
            <div class="text-sm text-text-secondary">Sedang Dipinjam</div>
        </div>
        <div class="bg-surface rounded-lg shadow-sm p-4">
            <div class="text-3xl font-bold text-danger-fg">{{ $terlambat }}</div>
            <div class="text-sm text-text-secondary">Terlambat</div>
        </div>
        <div class="bg-surface rounded-lg shadow-sm p-4">
            <div class="text-3xl font-bold text-text-primary">{{ $kembaliHariIni }}</div>
            <div class="text-sm text-text-secondary">Kembali Hari Ini</div>
        </div>
    </div>

    <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
        <h2 class="font-semibold text-text-primary mb-4">Peminjaman 7 Hari Terakhir</h2>
        <div class="flex items-end gap-2 h-40">
            @foreach ($grafik as $tanggal => $jumlah)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="text-xs text-text-secondary">{{ $jumlah }}</div>
                    <div class="w-full bg-accent rounded-t" style="height: {{ $jumlah > 0 ? ($jumlah / $maksGrafik) * 100 : 4 }}%"></div>
                    <div class="text-xs text-text-secondary">{{ \Carbon\Carbon::parse($tanggal)->format('d/m') }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('buku.index') }}" class="bg-surface rounded-lg shadow-sm p-4 text-center text-accent hover:bg-accent-soft font-medium">Tambah Buku</a>
        <a href="{{ route('peminjaman.index') }}" class="bg-surface rounded-lg shadow-sm p-4 text-center text-accent hover:bg-accent-soft font-medium">Pinjam Buku</a>
        <a href="{{ route('pengembalian.index') }}" class="bg-surface rounded-lg shadow-sm p-4 text-center text-accent hover:bg-accent-soft font-medium">Kembalikan Buku</a>
    </div>
</div>