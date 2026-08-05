<?php

use App\Exports\AnggotaExport;
use App\Exports\BukuExport;
use App\Exports\TransaksiExport;
use App\Models\Kategori;
use App\Models\Rak;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url]
    public int $page = 1;

    public $perPage = 10;

    public $tipe = 'buku';

    public $kategoriId = '';

    public $rakId = '';

    public $statusAnggota = '';

    public $jenisTransaksi = 'peminjaman';

    public $rows = [];

    public $headings = [];

    public $kategoriList = [];

    public $rakList = [];

    #[Url]
    public ?string $dariTanggal = null;

    #[Url]
    public ?string $sampaiTanggal = null;

    public function mount(): void
    {
        $this->kategoriList = Kategori::orderBy('nama')->get();
        $this->rakList = Rak::orderBy('kode')->get();
        $this->muatData();
    }

    public function updatedPerPage(): void
    {
        $this->page = 1;
    }

    public function updatedTipe(): void
    {
        $this->page = 1;
        $this->muatData();
    }

    public function updatedKategoriId(): void
    {
        $this->page = 1;
        $this->muatData();
    }

    public function updatedRakId(): void
    {
        $this->page = 1;
        $this->muatData();
    }

    public function updatedStatusAnggota(): void
    {
        $this->page = 1;
        $this->muatData();
    }

    public function updatedJenisTransaksi(): void
    {
        $this->page = 1;
        $this->muatData();
    }

    public function updatedDariTanggal(): void
    {
        $this->page = 1;
        $this->muatData();
    }

    public function updatedSampaiTanggal(): void
    {
        $this->page = 1;
        $this->muatData();
    }

    public function muatData(): void
    {
        $dari = $this->dariTanggal;
        $sampai = $this->sampaiTanggal;

        $export = match ($this->tipe) {
            'buku' => new BukuExport($this->kategoriId ?: null, $this->rakId ?: null),
            'anggota' => new AnggotaExport($this->statusAnggota ?: null, $dari, $sampai),
            default => new TransaksiExport($this->jenisTransaksi, $dari, $sampai),
        };

        $allRows = $export->collection();
        $this->headings = array_merge(['No'], $export->headings());
        $this->rows = $allRows->all();
    }

    public function render()
    {
        $paginator = (new LengthAwarePaginator(
            collect($this->rows)->forPage($this->page, $this->perPage)->values(),
            count($this->rows),
            $this->perPage,
            $this->page,
        ))->withPath(route('laporan.index'));

        return view('components.laporan.laporan', compact('paginator'));
    }
};
