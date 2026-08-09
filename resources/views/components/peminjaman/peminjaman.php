<?php

use App\Exceptions\AnggotaNonaktifException;
use App\Exceptions\AnggotaTerlambatException;
use App\Exceptions\MelebihiLimitException;
use App\Exceptions\StokHabisException;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Setting;
use App\Services\PeminjamanService;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $searchAnggota = '';

    public $hasilAnggota = [];

    public $anggotaDipilih = null;

    public $searchBuku = '';

    public $hasilBuku = [];

    public $cart = [];

    public $maksimalBuku = 3;

    public $lastPeminjamanId = null;

    public function mount(): void
    {
        $this->maksimalBuku = (int) Setting::get('maksimal_buku', 3);
    }

    public function resetForm(): void
    {
        $this->anggotaDipilih = null;
        $this->cart = [];
        $this->searchBuku = '';
        $this->searchAnggota = '';
        $this->lastPeminjamanId = null;
    }

    public function pilihAnggota($id): void
    {
        $this->anggotaDipilih = Anggota::findOrFail($id);
        $this->searchAnggota = '';
    }

    public function gantiAnggota(): void
    {
        $this->anggotaDipilih = null;
        $this->searchAnggota = '';
    }

    public function tambahBuku($id): void
    {
        $buku = Buku::findOrFail($id);

        if (collect($this->cart)->contains('id', $id)) {
            session()->flash('error', 'Buku sudah ada di daftar.');

            return;
        }

        if (count($this->cart) >= $this->maksimalBuku) {
            session()->flash('error', "Maksimal {$this->maksimalBuku} buku per peminjaman.");

            return;
        }

        if ($buku->stokTersedia() < 1) {
            session()->flash('error', "Stok buku \"{$buku->judul}\" habis.");

            return;
        }

        $this->cart[] = ['id' => $buku->id, 'kode' => $buku->kode, 'judul' => $buku->judul];
        $this->searchBuku = '';
    }

    public function hapusDariCart($index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function simpan(): void
    {
        if (! $this->anggotaDipilih) {
            session()->flash('error', 'Pilih anggota dulu.');

            return;
        }

        if (count($this->cart) === 0) {
            session()->flash('error', 'Pilih minimal satu buku.');

            return;
        }

        $bukuIds = array_column($this->cart, 'id');

        try {
            $peminjaman = app(PeminjamanService::class)
                ->buatPeminjaman($this->anggotaDipilih->id, $bukuIds, auth()->id());
        } catch (AnggotaNonaktifException|AnggotaTerlambatException|MelebihiLimitException|StokHabisException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        session()->flash('success', "Peminjaman {$peminjaman->no_transaksi} berhasil disimpan.");
        $this->lastPeminjamanId = $peminjaman->id;
        $this->anggotaDipilih = null;
        $this->cart = [];
        $this->searchBuku = '';
    }

    public function render()
    {
        $this->hasilAnggota = strlen($this->searchAnggota) >= 1
            ? Anggota::where(function ($q) {
                $q->where('nama', 'like', "%{$this->searchAnggota}%")
                    ->orWhere('nis', 'like', "%{$this->searchAnggota}%");
            })->limit(5)->get()
            : collect();

        $this->hasilBuku = strlen($this->searchBuku) >= 1
            ? Buku::with(['kategori', 'rak'])
                ->where('status', 'aktif')
                ->where(function ($q) {
                    $q->where('kode', 'like', "%{$this->searchBuku}%")
                        ->orWhere('isbn', 'like', "%{$this->searchBuku}%")
                        ->orWhere('judul', 'like', "%{$this->searchBuku}%");
                })->limit(5)->get()
            : collect();

        $transaksiHariIni = Peminjaman::with(['anggota', 'details'])
            ->whereDate('tanggal', today())
            ->orderByDesc('created_at')
            ->paginate(10, pageName: 'tpage');

        return view('components.peminjaman.peminjaman', [
            'transaksiHariIni' => $transaksiHariIni,
        ]);
    }
};
