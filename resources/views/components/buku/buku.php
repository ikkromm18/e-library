<?php

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Rak;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $showForm = false;
    public $showDetail = false;
    public $editId = null;
    public $detailBuku = null;

    // Search & filter
    public $search = '';
    public $filterKategori = '';
    public $filterRak = '';

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

    public $kategoriList = [];
    public $rakList = [];

    public function mount(): void
    {
        $this->kategoriList = Kategori::orderBy('nama')->get();
        $this->rakList = Rak::orderBy('kode')->get();
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

    public function queryBuku()
    {
        return Buku::with(['kategori', 'rak'])
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
        ];

        if ($this->editId) {
            $rules['isbn'] = 'nullable|string|unique:bukus,isbn,'.$this->editId;
        } else {
            $rules['isbn'] = 'nullable|string|unique:bukus,isbn';
        }

        $this->validate($rules);

        $data = [
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'sub_judul' => $this->sub_judul,
            'kategori_id' => $this->kategori_id,
            'pengarang' => $this->pengarang,
            'penerbit' => $this->penerbit,
            'tahun' => $this->tahun ?: null,
            'bahasa' => $this->bahasa,
            'rak_id' => $this->rak_id,
            'jumlah_eksemplar' => $this->jumlah_eksemplar,
            'deskripsi' => $this->deskripsi,
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
        $bukuList = $this->queryBuku()->paginate(10);

        return view('components.buku.buku', [
            'bukuList' => $bukuList,
            'kategoriList' => $this->kategoriList,
            'rakList' => $this->rakList,
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
    }
};
