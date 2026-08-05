<div wire:preserveScroll>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Laporan</h1>
    </div>

    <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
        <div class="flex gap-2 mb-4">
            <button wire:click="$set('tipe', 'buku')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'buku' ? 'bg-accent text-accent-fg' : 'bg-surface-muted text-text-primary' }}">Buku</button>
            <button wire:click="$set('tipe', 'anggota')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'anggota' ? 'bg-accent text-accent-fg' : 'bg-surface-muted text-text-primary' }}">Anggota</button>
            <button wire:click="$set('tipe', 'transaksi')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'transaksi' ? 'bg-accent text-accent-fg' : 'bg-surface-muted text-text-primary' }}">Transaksi</button>
        </div>

        <div class="flex flex-wrap gap-4 mb-4">
            @if ($tipe === 'buku')
                <select wire:model.live="kategoriId" class="rounded-md border-border shadow-sm text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach
                </select>
                <select wire:model.live="rakId" class="rounded-md border-border shadow-sm text-sm">
                    <option value="">Semua Rak</option>
                    @foreach ($rakList as $r)<option value="{{ $r->id }}">{{ $r->kode }}</option>@endforeach
                </select>
            @elseif ($tipe === 'anggota')
                <select wire:model.live="statusAnggota" class="rounded-md border-border shadow-sm text-sm">
                    <option value="">Semua Anggota</option>
                    <option value="aktif">Aktif</option>
                    <option value="lulus">Lulus</option>
                    <option value="pindah">Pindah</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
                <input type="date" wire:model.live="dariTanggal" class="rounded-md border-border shadow-sm text-sm">
                <span class="text-text-secondary self-center">s/d</span>
                <input type="date" wire:model.live="sampaiTanggal" class="rounded-md border-border shadow-sm text-sm">
            @else
                <select wire:model.live="jenisTransaksi" class="rounded-md border-border shadow-sm text-sm">
                    <option value="peminjaman">Peminjaman</option>
                    <option value="pengembalian">Pengembalian</option>
                    <option value="terlambat">Terlambat</option>
                </select>
                <input type="date" wire:model.live="dariTanggal" class="rounded-md border-border shadow-sm text-sm">
                <span class="text-text-secondary self-center">s/d</span>
                <input type="date" wire:model.live="sampaiTanggal" class="rounded-md border-border shadow-sm text-sm">
            @endif
        </div>

        <div class="flex gap-2 mb-4">
            <a href="{{ route('laporan.export', ['tipe' => $tipe, 'format' => 'pdf', 'kategori_id' => $kategoriId, 'rak_id' => $rakId, 'status' => $statusAnggota, 'jenis' => $jenisTransaksi, 'dari' => $dariTanggal, 'sampai' => $sampaiTanggal]) }}" class="bg-danger-solid text-danger-fg px-4 py-2 rounded-lg text-sm font-medium">Export PDF</a>
            <a href="{{ route('laporan.export', ['tipe' => $tipe, 'format' => 'excel', 'kategori_id' => $kategoriId, 'rak_id' => $rakId, 'status' => $statusAnggota, 'jenis' => $jenisTransaksi, 'dari' => $dariTanggal, 'sampai' => $sampaiTanggal]) }}" class="bg-accent text-accent-fg px-4 py-2 rounded-lg text-sm font-medium">Export Excel</a>
        </div>

        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface-muted">
                        <tr>
                            @foreach ($headings as $h)
                                <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary uppercase">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($paginator as $idx => $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-text-secondary">{{ ($page - 1) * $perPage + $idx + 1 }}</td>
                                @foreach ($row as $cell)
                                    <td class="px-4 py-2 text-sm text-text-primary">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($headings) }}" class="px-4 py-4 text-center text-sm text-text-secondary">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($paginator->hasPages())
                <div class="px-4 py-3 border-t border-border flex items-center justify-between">
                    <select wire:model.live="perPage" class="rounded-md border-border shadow-sm text-sm">
                        @foreach ([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                    {{ $paginator->links() }}
                </div>
                @endif
        </div>
    </div>
</div>