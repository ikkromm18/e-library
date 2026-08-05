<?php

use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use Livewire\Component;

new class extends Component
{
    public $search = '';
    public $hasilTransaksi = [];
    public $peminjaman = null;
    public $details = [];

    public function pilih($id): void
    {
        $this->peminjaman = Peminjaman::with(['anggota', 'petugas'])->findOrFail($id);
        $this->details = $this->peminjaman->details()->with('buku')->get();
    }

    public function kembalikan($detailId): void
    {
        $detail = PeminjamanDetail::findOrFail($detailId);
        $peminjaman = $detail->peminjaman;

        $detail->update([
            'tanggal_kembali' => today(),
            'keterlambatan_hari' => $peminjaman->hitungKeterlambatan() ?: null,
        ]);

        if ($peminjaman->sudahSelesai()) {
            $peminjaman->update(['status' => 'selesai']);
        }

        $this->details = $peminjaman->details()->with('buku')->get();
    }

    public function cariLagi(): void
    {
        $this->peminjaman = null;
        $this->details = [];
        $this->search = '';
    }

    public function render()
    {
        $this->hasilTransaksi = strlen($this->search) >= 1
            ? Peminjaman::with('anggota')
                ->where('status', 'dipinjam')
                ->where(function ($q) {
                    $q->where('no_transaksi', 'like', "%{$this->search}%")
                        ->orWhereHas('anggota', function ($q2) {
                            $q2->where('nama', 'like', "%{$this->search}%")
                                ->orWhere('nis', 'like', "%{$this->search}%");
                        });
                })->limit(5)->get()
            : collect();

        return view('components.pengembalian.pengembalian');
    }
};
