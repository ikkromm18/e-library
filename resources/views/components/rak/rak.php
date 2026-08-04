<?php

use App\Models\Rak;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    public $rakList;
    public $showForm = false;
    public $editId = null;

    #[Rule('required')]
    public $kode = '';

    #[Rule('required')]
    public $nama = '';

    public $keterangan = '';

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->rakList = Rak::withCount('bukus')->orderBy('kode')->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $rak = Rak::findOrFail($id);
        $this->editId = $id;
        $this->kode = $rak->kode;
        $this->nama = $rak->nama;
        $this->keterangan = $rak->keterangan;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'kode' => 'required|string|max:10'.($this->editId ? '|unique:raks,kode,'.$this->editId : '|unique:raks'),
            'nama' => 'required',
        ]);

        if ($this->editId) {
            Rak::findOrFail($this->editId)->update([
                'kode' => $this->kode,
                'nama' => $this->nama,
                'keterangan' => $this->keterangan,
            ]);
            session()->flash('success', 'Rak berhasil diupdate.');
        } else {
            Rak::create([
                'kode' => $this->kode,
                'nama' => $this->nama,
                'keterangan' => $this->keterangan,
            ]);
            session()->flash('success', 'Rak berhasil ditambahkan.');
        }

        $this->showForm = false;
        $this->loadData();
    }

    public function delete($id): void
    {
        $rak = Rak::findOrFail($id);

        if ($rak->bukus()->count() > 0) {
            session()->flash('error', 'Tidak bisa hapus rak yang masih memiliki buku.');
            return;
        }

        $rak->delete();
        $this->loadData();
        session()->flash('success', 'Rak berhasil dihapus.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('components.rak.rak');
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->kode = '';
        $this->nama = '';
        $this->keterangan = '';
    }
};
