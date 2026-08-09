<div wire:preserveScroll>
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
                    <div class="text-lg font-semibold text-text-primary flex items-center gap-2">
                        {{ $peminjaman->no_transaksi }}
                        <a href="{{ route('peminjaman.print', $peminjaman->id) }}" target="_blank" class="inline-flex items-center px-2.5 py-1 bg-accent hover:bg-accent-hover text-accent-fg text-xs font-medium rounded shadow-sm transition">
                            🖨️ Cetak Bukti
                        </a>
                    </div>
                    <div class="text-sm text-text-secondary mt-1">
                        {{ $peminjaman->anggota->nama }} ({{ $peminjaman->anggota->nis }}) &middot;
                        Petugas: {{ $peminjaman->petugas->name }} &middot;
                        Pinjam: {{ $peminjaman->tanggal->format('d/m/Y') }} &middot;
                        Jatuh Tempo: {{ $peminjaman->tanggal_jatuh_tempo->format('d/m/Y') }}
                    </div>
                </div>
                <button wire:click="cariLagi" wire:loading.attr="disabled" class="text-sm text-danger-fg hover:text-danger-fg disabled:opacity-50">Cari Lain</button>
            </div>

            <table class="min-w-full divide-y divide-border">
                <thead class="bg-surface-muted">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">Buku</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">Tgl Dikembalikan</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($details as $detail)
                        <tr>
                            <td class="px-4 py-2 text-sm text-text-secondary">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 text-sm text-text-primary">{{ $detail->buku->judul }}</td>
                            <td class="px-4 py-2 text-sm text-text-secondary">
                                @if ($detail->tanggal_kembali)
                                    {{ $detail->tanggal_kembali->format('d/m/Y') }}
                                @else
                                    <span class="text-text-secondary">—</span>
                                @endif
                            </td>
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
                                    <button wire:click="kembalikan({{ $detail->id }})" wire:loading.attr="disabled" class="text-accent hover:text-accent-hover text-sm disabled:opacity-50">Kembalikan</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
            <div class="relative">
                <input type="text" wire:model.live="search" placeholder="Cari no transaksi / nama / NIS anggota..." class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
                @if ($search)
                    <ul class="absolute z-20 w-full bg-surface border border-border rounded-md shadow-lg mt-1 px-3 py-1 divide-y divide-border max-h-60 overflow-y-auto">
                        @forelse ($hasilTransaksi as $t)
                            <li wire:click="pilih({{ $t->id }})" class="flex items-center justify-between py-2 px-2 cursor-pointer hover:bg-surface-muted transition-colors rounded">
                                <span class="text-text-primary">{{ $t->no_transaksi }} <span class="text-sm text-text-secondary">&mdash; {{ $t->anggota->nama }}</span></span>
                                <span class="text-accent text-sm font-medium">Pilih</span>
                            </li>
                        @empty
                            <li class="py-2 text-sm text-text-secondary">Tidak ditemukan transaksi aktif.</li>
                        @endforelse
                    </ul>
                @endif
            </div>
        </div>
    @endif
</div>
