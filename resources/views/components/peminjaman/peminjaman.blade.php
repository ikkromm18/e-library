<div wire:preserveScroll>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Peminjaman Buku</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-success border border-success text-success-fg px-4 py-3 rounded mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>{{ session('success') }}</div>
            @if ($lastPeminjamanId)
                <div class="flex items-center gap-2">
                    <a href="{{ route('peminjaman.print', $lastPeminjamanId) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-accent hover:bg-accent-hover text-accent-fg text-sm font-medium rounded-md shadow-sm transition">
                        🖨️ Cetak Bukti
                    </a>
                    <button wire:click="resetForm" class="inline-flex items-center px-3 py-1.5 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-muted transition">
                        Peminjaman Baru
                    </button>
                </div>
            @endif
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-danger border border-danger-fg text-danger-fg px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
        <h2 class="font-semibold text-text-primary mb-2">1. Cari Anggota</h2>
        @if ($anggotaDipilih)
            <div class="flex items-center justify-between bg-success border border-success rounded p-3">
                <div>
                    <div class="font-medium text-text-primary">{{ $anggotaDipilih->nama }}</div>
                    <div class="text-sm text-text-secondary">NIS: {{ $anggotaDipilih->nis }} &middot; Kelas: {{ $anggotaDipilih->kelas }} &middot; {{ $anggotaDipilih->status }}</div>
                </div>
                <button wire:click="gantiAnggota" wire:loading.attr="disabled" class="text-sm text-danger-fg hover:text-danger-fg disabled:opacity-50">Ganti</button>
            </div>
        @else
            <div class="relative">
                <input type="text" wire:model.live="searchAnggota" placeholder="Cari nama / NIS anggota..." class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
                @if ($searchAnggota)
                    <ul class="absolute z-20 w-full bg-surface border border-border rounded-md shadow-lg mt-1 px-3 py-1 divide-y divide-border max-h-60 overflow-y-auto">
                        @forelse ($hasilAnggota as $a)
                            <li wire:click="pilihAnggota({{ $a->id }})" class="flex items-center justify-between py-2 px-2 cursor-pointer hover:bg-surface-muted transition-colors rounded">
                                <span class="text-text-primary">{{ $a->nama }} <span class="text-sm text-text-secondary">({{ $a->nis }} &mdash; {{ $a->kelas }})</span></span>
                                <span class="text-accent text-sm font-medium">Pilih</span>
                            </li>
                        @empty
                            <li class="py-2 text-sm text-text-secondary">Tidak ditemukan.</li>
                        @endforelse
                    </ul>
                @endif
            </div>
        @endif
    </div>

    <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
        <h2 class="font-semibold text-text-primary mb-2">2. Pilih Buku (maksimal {{ $maksimalBuku }})</h2>
        <div class="relative">
            <input type="text" wire:model.live="searchBuku" placeholder="Cari kode / ISBN / judul..." class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
            @if ($searchBuku)
                <ul class="absolute z-20 w-full bg-surface border border-border rounded-md shadow-lg mt-1 px-3 py-1 divide-y divide-border max-h-60 overflow-y-auto">
                    @forelse ($hasilBuku as $b)
                        <li wire:click="tambahBuku({{ $b->id }})" class="flex items-center justify-between py-2 px-2 cursor-pointer hover:bg-surface-muted transition-colors rounded">
                            <div>
                                <div class="text-sm font-medium text-text-primary">{{ $b->judul }}</div>
                                <div class="text-sm text-text-secondary">{{ $b->kode }} &mdash; stok {{ $b->stokTersedia() }}/{{ $b->jumlah_eksemplar }}</div>
                            </div>
                            <span class="text-accent text-sm font-medium">Tambah</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-text-secondary">Tidak ditemukan.</li>
                    @endforelse
                </ul>
            @endif
        </div>
    </div>

    <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
        <h2 class="font-semibold text-text-primary mb-2">3. Daftar Buku</h2>
        @if (count($cart) === 0)
            <p class="text-sm text-text-secondary">Belum ada buku dipilih.</p>
        @else
            <table class="min-w-full divide-y divide-border mb-4">
                <thead class="bg-surface-muted">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">Kode</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">Judul</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($cart as $index => $item)
                        <tr>
                            <td class="px-4 py-2 text-sm text-text-secondary">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 text-sm font-mono text-text-primary">{{ $item['kode'] }}</td>
                            <td class="px-4 py-2 text-sm text-text-primary">{{ $item['judul'] }}</td>
                            <td class="px-4 py-2 text-right">
                                <button wire:click="hapusDariCart({{ $index }})" wire:loading.attr="disabled" class="text-danger-fg hover:text-danger-fg text-sm disabled:opacity-50">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button wire:click="simpan" wire:loading.attr="disabled" @if (! $anggotaDipilih) disabled @endif class="bg-accent hover:bg-accent-hover text-accent-fg px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">Simpan Peminjaman</button>
        @endif
    </div>

    {{-- Daftar Transaksi Hari Ini --}}
    <div class="bg-surface rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-text-primary">Transaksi Hari Ini</h2>
            <span class="text-xs text-text-secondary bg-surface-muted px-2 py-1 rounded-full">{{ $transaksiHariIni->total() }} transaksi</span>
        </div>

        @if ($transaksiHariIni->isEmpty())
            <p class="text-sm text-text-secondary text-center py-6">Belum ada transaksi hari ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface-muted">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">No. Transaksi</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Anggota</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Jatuh Tempo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Tgl Dikembalikan</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($transaksiHariIni as $t)
                            <tr class="hover:bg-surface-muted transition-colors">
                                <td class="px-4 py-3 text-sm font-mono font-medium text-text-primary">{{ $t->no_transaksi }}</td>
                                <td class="px-4 py-3 text-sm text-text-primary">
                                    {{ $t->anggota->nama }}
                                    <span class="text-text-secondary text-xs ml-1">({{ $t->anggota->nis }})</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-secondary">{{ $t->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-text-secondary">
                                    @php
                                        $tglKembali = $t->details->whereNotNull('tanggal_kembali')->max('tanggal_kembali');
                                    @endphp
                                    @if ($tglKembali)
                                        {{ \Carbon\Carbon::parse($tglKembali)->format('d/m/Y') }}
                                    @else
                                        <span class="text-text-secondary">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($t->status === 'dipinjam')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-accent/10 text-accent">Dipinjam</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-success text-success-fg">Selesai</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('peminjaman.print', $t->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-accent border border-accent/40 rounded hover:bg-accent/10 transition">
                                        🖨️ Cetak
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($transaksiHariIni->hasPages())
                <div class="mt-4 flex items-center justify-between text-sm text-text-secondary">
                    <span>
                        Menampilkan {{ $transaksiHariIni->firstItem() }}–{{ $transaksiHariIni->lastItem() }}
                        dari {{ $transaksiHariIni->total() }} transaksi
                    </span>
                    <div class="flex items-center gap-1">
                        @if ($transaksiHariIni->onFirstPage())
                            <span class="px-3 py-1 rounded-md bg-surface-muted text-text-secondary opacity-50 text-xs cursor-not-allowed">← Sebelumnya</span>
                        @else
                            <a wire:navigate href="{{ $transaksiHariIni->previousPageUrl() }}"
                               class="px-3 py-1 rounded-md bg-surface-muted hover:bg-border text-text-secondary text-xs transition">← Sebelumnya</a>
                        @endif

                        @if ($transaksiHariIni->hasMorePages())
                            <a wire:navigate href="{{ $transaksiHariIni->nextPageUrl() }}"
                               class="px-3 py-1 rounded-md bg-surface-muted hover:bg-border text-text-secondary text-xs transition">Berikutnya →</a>
                        @else
                            <span class="px-3 py-1 rounded-md bg-surface-muted text-text-secondary opacity-50 text-xs cursor-not-allowed">Berikutnya →</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
