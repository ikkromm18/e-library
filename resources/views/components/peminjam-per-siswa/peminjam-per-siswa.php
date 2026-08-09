<?php

use App\Models\Anggota;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $search = '';

    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function queryPeminjam()
    {
        return Anggota::query()
            ->has('peminjamen')
            ->withCount([
                'peminjamen as total_transaksi',
                'peminjamen as pinjaman_aktif_count' => function ($q) {
                    $q->where('status', 'dipinjam');
                },
            ])
            ->with([
                'peminjamen' => function ($q) {
                    $q->orderBy('created_at', 'desc')->with(['details.buku', 'petugas']);
                },
            ])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nis', 'like', "%{$this->search}%")
                        ->orWhere('nama', 'like', "%{$this->search}%")
                        ->orWhere('kelas', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus === 'aktif', function ($q) {
                $q->whereHas('peminjamen', fn ($p) => $p->where('status', 'dipinjam'));
            })
            ->when($this->filterStatus === 'terlambat', function ($q) {
                $q->whereHas('peminjamen', fn ($p) => $p->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', today()));
            })
            ->orderBy('nama');
    }

    public function render()
    {
        return view('components.peminjam-per-siswa.peminjam-per-siswa', [
            'peminjamList' => $this->queryPeminjam()->paginate($this->perPage),
        ]);
    }
};
