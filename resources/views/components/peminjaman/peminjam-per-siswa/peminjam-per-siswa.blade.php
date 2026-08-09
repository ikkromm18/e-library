<div class="bg-surface p-6 rounded-lg shadow border border-border space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-text-primary">Daftar Peminjam Buku per Siswa</h3>
            <p class="text-sm text-text-secondary">Daftar anggota perpustakaan beserta riwayat dan status pinjamannya.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[240px]">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari NIS, Nama, atau Kelas..." 
                    class="w-full pl-9 pr-4 py-2 border border-border rounded-lg text-sm bg-surface text-text-primary focus:ring-2 focus:ring-primary focus:border-transparent"
                />
                <svg class="w-4 h-4 absolute left-3 top-3 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select wire:model.live="filterStatus" class="border border-border rounded-lg text-sm px-3 py-2 bg-surface text-text-primary focus:ring-2 focus:ring-primary">
                <option value="">Semua Status</option>
                <option value="aktif">Memiliki Pinjaman Aktif</option>
                <option value="terlambat">Ada Keterlambatan</option>
            </select>

            <select wire:model.live="perPage" class="border border-border rounded-lg text-sm px-3 py-2 bg-surface text-text-primary focus:ring-2 focus:ring-primary">
                <option value="10">10 / hal</option>
                <option value="25">25 / hal</option>
                <option value="50">50 / hal</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto border border-border rounded-lg">
        <table class="w-full text-sm text-left text-text-primary">
            <thead class="bg-surface-muted text-xs uppercase text-text-secondary border-b border-border">
                <tr>
                    <th class="p-3 w-10 text-center">#</th>
                    <th class="p-3">NIS</th>
                    <th class="p-3">Nama Siswa</th>
                    <th class="p-3">Kelas</th>
                    <th class="p-3 text-center">Total Transaksi</th>
                    <th class="p-3 text-center">Status Pinjaman</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($peminjamList as $siswa)
                    <tr x-data="{ expanded: false }" class="hover:bg-surface-muted/50 transition-colors">
                        <td class="p-3 text-center">
                            <button 
                                @click="expanded = !expanded" 
                                class="p-1 rounded-md hover:bg-surface-muted text-text-secondary focus:outline-none transition-transform duration-200"
                                :class="{ 'rotate-90': expanded }"
                                title="Lihat detail buku"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </td>
                        <td class="p-3 font-mono font-medium">{{ $siswa->nis }}</td>
                        <td class="p-3 font-semibold">{{ $siswa->nama }}</td>
                        <td class="p-3">{{ $siswa->kelas ?? '-' }}</td>
                        <td class="p-3 text-center font-medium">{{ $siswa->total_transaksi }} Transaksi</td>
                        <td class="p-3 text-center">
                            @if ($siswa->pinjaman_aktif_count > 0)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                    {{ $siswa->pinjaman_aktif_count }} Pinjaman Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Bebas Pinjaman
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <button 
                                @click="expanded = !expanded" 
                                class="text-xs text-primary font-medium hover:underline inline-flex items-center gap-1"
                            >
                                <span x-text="expanded ? 'Sembunyikan' : 'Detail Buku'"></span>
                            </button>
                        </td>

                        <tr x-show="expanded" x-cloak class="bg-surface-muted/30 border-t border-b border-border">
                            <td colspan="7" class="p-4 sm:p-6">
                                <div class="bg-surface border border-border rounded-md p-4 space-y-4 shadow-inner">
                                    <h4 class="font-bold text-sm text-text-primary flex items-center gap-2">
                                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        Riwayat Peminjaman Buku - {{ $siswa->nama }} ({{ $siswa->nis }})
                                    </h4>

                                    @if ($siswa->peminjamen->isEmpty())
                                        <p class="text-sm text-text-secondary italic">Belum ada riwayat peminjaman.</p>
                                    @else
                                        <div class="space-y-4">
                                            @foreach ($siswa->peminjamen as $peminjaman)
                                                <div class="border border-border rounded-md p-3 bg-surface text-xs space-y-2">
                                                    <div class="flex flex-wrap justify-between items-center border-b border-border pb-2 gap-2">
                                                        <div class="flex items-center gap-3">
                                                            <span class="font-mono font-bold text-text-primary">{{ $peminjaman->no_transaksi }}</span>
                                                            <span class="text-text-secondary">Tgl Pinjam: <strong>{{ $peminjaman->tanggal->format('d/m/Y') }}</strong></span>
                                                            <span class="text-text-secondary">Jatuh Tempo: <strong>{{ $peminjaman->tanggal_jatuh_tempo->format('d/m/Y') }}</strong></span>
                                                        </div>
                                                        <div>
                                                            @if ($peminjaman->status === 'dipinjam')
                                                                @if (now()->startOfDay()->gt($peminjaman->tanggal_jatuh_tempo))
                                                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300 font-semibold">
                                                                        Terlambat ({{ $peminjaman->hitungKeterlambatan() }} hari)
                                                                    </span>
                                                                @else
                                                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 font-semibold">
                                                                        Sedang Dipinjam
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 font-semibold">
                                                                    Selesai
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="pt-1">
                                                        <p class="font-medium text-text-secondary mb-1">Buku yang dipinjam:</p>
                                                        <ul class="divide-y divide-border/50">
                                                            @foreach ($peminjaman->details as $detail)
                                                                <li class="py-1.5 flex justify-between items-center">
                                                                    <div>
                                                                        <span class="font-semibold text-text-primary">{{ $detail->buku->judul ?? 'Buku dihapus' }}</span>
                                                                        <span class="text-text-secondary ml-2 font-mono">({{ $detail->buku->kode_buku ?? '-' }})</span>
                                                                    </div>
                                                                    <div>
                                                                        @if ($detail->tanggal_kembali)
                                                                            <span class="text-emerald-600 dark:text-emerald-400">
                                                                                Dikembalikan: {{ $detail->tanggal_kembali->format('d/m/Y') }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-amber-600 dark:text-amber-400">Belum Kembali</span>
                                                                        @endif
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-text-secondary">
                            Tidak ada data peminjam yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $peminjamList->links() }}
    </div>
</div>
