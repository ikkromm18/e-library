<div wire:preserveScroll>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Peminjaman Buku</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-success border border-success text-success-fg px-4 py-3 rounded mb-4">{{ session('success') }}</div>
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
                <button wire:click="gantiAnggota" class="text-sm text-danger-fg hover:text-danger-fg">Ganti</button>
            </div>
        @else
            <input type="text" wire:model.live="searchAnggota" placeholder="Cari nama / NIS anggota..." class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
            @if ($searchAnggota)
                <ul class="mt-2 divide-y divide-border">
                    @forelse ($hasilAnggota as $a)
                        <li class="flex items-center justify-between py-2">
                            <span class="text-text-primary">{{ $a->nama }} <span class="text-sm text-text-secondary">({{ $a->nis }} &mdash; {{ $a->kelas }})</span></span>
                            <button wire:click="pilihAnggota({{ $a->id }})" class="text-accent hover:text-accent-hover text-sm">Pilih</button>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-text-secondary">Tidak ditemukan.</li>
                    @endforelse
                </ul>
            @endif
        @endif
    </div>

    <div class="bg-surface rounded-lg shadow-sm p-4 mb-6">
        <h2 class="font-semibold text-text-primary mb-2">2. Pilih Buku (maksimal {{ $maksimalBuku }})</h2>
        <input type="text" wire:model.live="searchBuku" placeholder="Cari kode / ISBN / judul..." class="block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent text-sm">
        @if ($searchBuku)
            <ul class="mt-2 divide-y divide-border">
                @forelse ($hasilBuku as $b)
                    <li class="flex items-center justify-between py-2">
                        <div>
                            <div class="text-sm font-medium text-text-primary">{{ $b->judul }}</div>
                            <div class="text-sm text-text-secondary">{{ $b->kode }} &mdash; stok {{ $b->stokTersedia() }}/{{ $b->jumlah_eksemplar }}</div>
                        </div>
                        <button wire:click="tambahBuku({{ $b->id }})" class="text-accent hover:text-accent-hover text-sm">Tambah</button>
                    </li>
                @empty
                    <li class="py-2 text-sm text-text-secondary">Tidak ditemukan.</li>
                @endforelse
            </ul>
        @endif
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
                                <button wire:click="hapusDariCart({{ $index }})" class="text-danger-fg hover:text-danger-fg text-sm">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button wire:click="simpan" @if (! $anggotaDipilih) disabled @endif class="bg-accent hover:bg-accent-hover text-accent-fg px-4 py-2 rounded-lg text-sm font-medium">Simpan Peminjaman</button>
        @endif
    </div>
</div>
