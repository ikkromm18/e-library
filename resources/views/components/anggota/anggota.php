<?php

use App\Models\Anggota;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $showForm = false;
    public $showDetail = false;
    public $editId = null;
    public $detailAnggota = null;

    // Search & filter
    public $search = '';
    public $filterStatus = '';

    // Form fields
    public $nis = '';
    public $nama = '';
    public $jenis_kelamin = 'L';
    public $kelas = '';
    public $alamat = '';
    public $no_hp = '';
    public $tanggal_masuk = '';
    public $status = 'aktif';

    public function mount(): void
    {
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function queryAnggota()
    {
        return Anggota::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('nis', 'like', "%{$this->search}%")
                        ->orWhere('nama', 'like', "%{$this->search}%")
                        ->orWhere('kelas', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('nama');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $anggota = Anggota::findOrFail($id);
        $this->editId = $id;
        $this->nis = $anggota->nis;
        $this->nama = $anggota->nama;
        $this->jenis_kelamin = $anggota->jenis_kelamin;
        $this->kelas = $anggota->kelas;
        $this->alamat = $anggota->alamat;
        $this->no_hp = $anggota->no_hp;
        $this->tanggal_masuk = $anggota->tanggal_masuk->format('Y-m-d');
        $this->status = $anggota->status;
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'nis' => 'required|string|unique:anggota,nis' . ($this->editId ? ',' . $this->editId : ''),
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas' => 'nullable|string|max:10',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
            'status' => 'required|in:aktif,lulus,pindah,nonaktif',
        ];

        $this->validate($rules);

        $data = [
            'nis' => $this->nis,
            'nama' => $this->nama,
            'jenis_kelamin' => $this->jenis_kelamin,
            'kelas' => $this->kelas,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'tanggal_masuk' => $this->tanggal_masuk,
            'status' => $this->status,
        ];

        if ($this->editId) {
            Anggota::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Anggota berhasil diupdate.');
        } else {
            Anggota::create($data);
            session()->flash('success', 'Anggota berhasil ditambahkan.');
        }

        $this->showForm = false;
    }

    public function detail($id): void
    {
        $this->detailAnggota = Anggota::findOrFail($id);
        $this->showDetail = true;
    }

    public function delete($id): void
    {
        $anggota = Anggota::findOrFail($id);

        if ($anggota->peminjamen()->exists()) {
            session()->flash('error', 'Tidak bisa hapus anggota yang memiliki riwayat peminjaman.');
            return;
        }

        $anggota->delete();
        session()->flash('success', 'Anggota berhasil dihapus.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('components.anggota.anggota', [
            'anggotaList' => $this->queryAnggota()->paginate(10),
        ]);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->nis = '';
        $this->nama = '';
        $this->jenis_kelamin = 'L';
        $this->kelas = '';
        $this->alamat = '';
        $this->no_hp = '';
        $this->tanggal_masuk = '';
        $this->status = 'aktif';
    }
};
