<?php

use App\Models\Kategori;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    public $kategoriList;
    public $showForm = false;
    public $editId = null;

    #[Rule('required')]
    public $nama = '';

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->kategoriList = Kategori::withCount('bukus')->orderBy('nama')->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $kategori = Kategori::findOrFail($id);
        $this->editId = $id;
        $this->nama = $kategori->nama;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editId) {
            Kategori::findOrFail($this->editId)->update(['nama' => $this->nama]);
            session()->flash('success', 'Kategori berhasil diupdate.');
        } else {
            Kategori::create(['nama' => $this->nama]);
            session()->flash('success', 'Kategori berhasil ditambahkan.');
        }

        $this->showForm = false;
        $this->loadData();
    }

    public function delete($id): void
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->bukus()->count() > 0) {
            session()->flash('error', 'Tidak bisa hapus kategori yang masih memiliki buku.');
            return;
        }

        $kategori->delete();
        $this->loadData();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('components.kategori.kategori');
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->nama = '';
    }
};
