<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Pengembalian Buku</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-success border border-success text-success-fg px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if ($peminjaman)
        <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="text-lg font-semibold text-text-primary">{{ $peminjaman->no_transaksi }}</div>
                    <div class="text-sm text-text-secondary">
                        {{ $peminjaman->anggota->nama }} ({{ $peminjaman->anggota->nis }}) &middot;
                        Petugas: {{ $peminjaman->petugas->name }} &middot;
                        Pinjam: {{ $peminjaman->tanggal->format('d/m/Y') }} &middot;
                        Jatuh Tempo: {{ $peminjaman->tanggal_jatuh_tempo->format('d/m/Y') }}
                    </div>
                </div>
                <button wire:click="cariLagi" class="text-sm text-danger-fg hover:text-danger-fg">Cari Lain</button>
            </div>

            <table class="min-w-full divide-y divide-border">
                <thead class="bg-surface-muted">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">Buku</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($details as $detail)
                        <tr>
                            <td class="px-4 py-2 text-sm text-text-primary">{{ $detail->buku->judul }}</td>
                            <td class="px-4 py-2 text-sm">
                                @if ($detail->tanggal_kembali)
                                    @if ($detail->keterlambatan_hari > 0)
                                        <span class="px-2 py-1 rounded-full text-xs bg-danger text-danger-fg">Terlambat {{ $detail->keterlambatan_hari }} Hari</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-success text-success-fg">Tepat Waktu</span>
                                    @endif
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs bg-surface-muted text-text-secondary">Belum Kembali</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                @if (! $detail->tanggal_kembali)
                                    <button wire:click="kembalikan({{ $detail->id }})" class="text-accent hover:text-accent-hover text-sm">Kembalikan</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
            <input type="text" wire:model.live="search" placeholder="Cari no transaksi / nama / NIS anggota..." class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
            @if ($search)
                <ul class="mt-2 divide-y divide-border">
                    @forelse ($hasilTransaksi as $t)
                        <li class="flex items-center justify-between py-2">
                            <span class="text-text-primary">{{ $t->no_transaksi }} <span class="text-sm text-text-secondary">&mdash; {{ $t->anggota->nama }}</span></span>
                            <button wire:click="pilih({{ $t->id }})" class="text-accent hover:text-accent-hover text-sm">Pilih</button>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-text-secondary">Tidak ditemukan transaksi aktif.</li>
                    @endforelse
                </ul>
            @endif
        </div>
    @endif
</div>
