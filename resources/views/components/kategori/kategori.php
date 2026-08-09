<?php

use App\Models\Kategori;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public $showForm = false;

    public $editId = null;

    #[Rule('required')]
    public $nama = '';

    public function updatedPerPage(): void
    {
        $this->resetPage();
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
    }

    public function delete($id): void
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->bukus()->count() > 0) {
            session()->flash('error', 'Tidak bisa hapus kategori yang masih memiliki buku.');

            return;
        }

        $kategori->delete();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        $kategoriList = Kategori::withCount('bukus')->orderBy('nama')->paginate($this->perPage);

        return view('components.kategori.kategori', compact('kategoriList'));
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->nama = '';
    }
};
