<?php

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\PeminjamanDetail;
use App\Models\Rak;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads, WithPagination;

    public int $perPage = 10;

    public $showForm = false;

    public $showDetail = false;

    public $editId = null;

    public $detailBuku = null;

    // Search & filter
    public $search = '';

    public $filterKategori = '';

    public $filterRak = '';

    public $filterPengarang = '';

    public $filterPenerbit = '';

    public $filterTahun = '';

    // Form fields
    public $kode = '';

    public $isbn = '';

    public $judul = '';

    public $sub_judul = '';

    public $kategori_id = '';

    public $pengarang = '';

    public $penerbit = '';

    public $tahun = '';

    public $bahasa = '';

    public $rak_id = '';

    public $jumlah_eksemplar = 1;

    public $deskripsi = '';

    public $status = 'aktif';

    public $cover;

    public $coverPath = null;

    public $kategoriList = [];

    public $rakList = [];

    public $pengarangList = [];

    public $penerbitList = [];

    public $tahunList = [];

    public function mount(): void
    {
        $this->kategoriList = Kategori::orderBy('nama')->get();
        $this->rakList = Rak::orderBy('kode')->get();
        $this->pengarangList = Buku::whereNotNull('pengarang')->distinct()->orderBy('pengarang')->pluck('pengarang');
        $this->penerbitList = Buku::whereNotNull('penerbit')->distinct()->orderBy('penerbit')->pluck('penerbit');
        $this->tahunList = Buku::whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterKategori(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRak(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function queryBuku()
    {
        return Buku::with(['kategori', 'rak'])->withCount(['detailsAktif as stok_dipakai'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('kode', 'like', "%{$this->search}%")
                        ->orWhere('isbn', 'like', "%{$this->search}%")
                        ->orWhere('judul', 'like', "%{$this->search}%")
                        ->orWhere('pengarang', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterKategori, fn ($q) => $q->where('kategori_id', $this->filterKategori))
            ->when($this->filterRak, fn ($q) => $q->where('rak_id', $this->filterRak))
            ->when($this->filterPengarang, fn ($q) => $q->where('pengarang', $this->filterPengarang))
            ->when($this->filterPenerbit, fn ($q) => $q->where('penerbit', $this->filterPenerbit))
            ->when($this->filterTahun, fn ($q) => $q->where('tahun', $this->filterTahun))
            ->orderBy('kode');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->kode = Buku::buatKode();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $buku = Buku::findOrFail($id);
        $this->editId = $id;
        $this->fillFromModel($buku);
        $this->coverPath = $buku->cover;
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'judul' => 'required',
            'kategori_id' => 'required|exists:kategoris,id',
            'rak_id' => 'required|exists:raks,id',
            'jumlah_eksemplar' => 'required|integer|min:1',
            'status' => 'required|in:aktif,tidak',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1024',
        ];

        if ($this->editId) {
            $rules['isbn'] = 'nullable|string|unique:bukus,isbn,'.$this->editId;
            $dipinjam = PeminjamanDetail::where('buku_id', $this->editId)->whereNull('tanggal_kembali')->count();
            if ($this->jumlah_eksemplar < $dipinjam) {
                session()->flash('error', "Jumlah eksemplar tidak boleh kurang dari jumlah yang sedang dipinjam ($dipinjam).");

                return;
            }
        } else {
            $rules['isbn'] = 'nullable|string|unique:bukus,isbn';
        }

        $this->validate($rules);

        $data = [
            'isbn' => $this->isbn ?: null,
            'judul' => $this->judul,
            'sub_judul' => $this->sub_judul ?: null,
            'kategori_id' => $this->kategori_id,
            'pengarang' => $this->pengarang ?: null,
            'penerbit' => $this->penerbit ?: null,
            'tahun' => $this->tahun ?: null,
            'bahasa' => $this->bahasa ?: null,
            'rak_id' => $this->rak_id,
            'jumlah_eksemplar' => $this->jumlah_eksemplar,
            'deskripsi' => $this->deskripsi ?: null,
            'status' => $this->status,
        ];

        if ($this->cover) {
            $data['cover'] = $this->cover->store('covers', 'public');
        }

        if ($this->editId) {
            Buku::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Buku berhasil diupdate.');
        } else {
            $data['kode'] = $this->kode;
            Buku::create($data);
            session()->flash('success', 'Buku berhasil ditambahkan.');
        }

        $this->showForm = false;
    }

    public function detail($id): void
    {
        $this->detailBuku = Buku::with(['kategori', 'rak'])->findOrFail($id);
        $this->showDetail = true;
    }

    public function delete($id): void
    {
        if (PeminjamanDetail::where('buku_id', $id)->exists()) {
            session()->flash('error', 'Tidak bisa hapus buku yang memiliki riwayat peminjaman.');

            return;
        }

        Buku::findOrFail($id)->delete();
        session()->flash('success', 'Buku berhasil dihapus.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        $bukuList = $this->queryBuku()->paginate($this->perPage);

        return view('components.buku.buku', [
            'bukuList' => $bukuList,
            'kategoriList' => $this->kategoriList,
            'rakList' => $this->rakList,
            'pengarangList' => $this->pengarangList,
            'penerbitList' => $this->penerbitList,
            'tahunList' => $this->tahunList,
        ]);
    }

    private function fillFromModel(Buku $buku): void
    {
        $this->kode = $buku->kode;
        $this->isbn = $buku->isbn;
        $this->judul = $buku->judul;
        $this->sub_judul = $buku->sub_judul;
        $this->kategori_id = $buku->kategori_id;
        $this->pengarang = $buku->pengarang;
        $this->penerbit = $buku->penerbit;
        $this->tahun = $buku->tahun;
        $this->bahasa = $buku->bahasa;
        $this->rak_id = $buku->rak_id;
        $this->jumlah_eksemplar = $buku->jumlah_eksemplar;
        $this->deskripsi = $buku->deskripsi;
        $this->status = $buku->status;
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->kode = '';
        $this->isbn = '';
        $this->judul = '';
        $this->sub_judul = '';
        $this->kategori_id = '';
        $this->pengarang = '';
        $this->penerbit = '';
        $this->tahun = '';
        $this->bahasa = '';
        $this->rak_id = '';
        $this->jumlah_eksemplar = 1;
        $this->deskripsi = '';
        $this->status = 'aktif';
        $this->cover = null;
        $this->coverPath = null;
    }
};
