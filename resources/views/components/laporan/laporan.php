<?php

use App\Exports\AnggotaExport;
use App\Exports\BukuExport;
use App\Exports\TransaksiExport;
use App\Models\Kategori;
use App\Models\Rak;
use Livewire\Component;

new class extends Component
{
    public $tipe = 'buku';

    public $kategoriId = '';

    public $rakId = '';

    public $statusAnggota = '';

    public $jenisTransaksi = 'peminjaman';

    public $rows = [];

    public $headings = [];

    public $kategoriList = [];

    public $rakList = [];

    public function mount(): void
    {
        $this->kategoriList = Kategori::orderBy('nama')->get();
        $this->rakList = Rak::orderBy('kode')->get();
        $this->muatData();
    }

    public function updated(): void
    {
        $this->muatData();
    }

    public function muatData(): void
    {
        $export = match ($this->tipe) {
            'buku' => new BukuExport($this->kategoriId ?: null, $this->rakId ?: null),
            'anggota' => new AnggotaExport($this->statusAnggota ?: null),
            default => new TransaksiExport($this->jenisTransaksi),
        };

        $this->rows = $export->collection();
        $this->headings = $export->headings();
    }

    public function render()
    {
        return view('components.laporan.laporan');
    }
};
