<?php

use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $namaPerpus = '';

    public $logo = null;

    public $logoPath = null;

    public $lamaPinjam = 7;

    public $maksimalBuku = 3;

    public function mount(): void
    {
        $this->namaPerpus = Setting::get('nama_perpus', 'Perpustakaan');
        $this->logoPath = Setting::get('logo', null);
        $this->lamaPinjam = (int) Setting::get('lama_pinjam', 7);
        $this->maksimalBuku = (int) Setting::get('maksimal_buku', 3);
    }

    public function simpan(): void
    {
        $this->validate([
            'namaPerpus' => 'required|string|max:255',
            'lamaPinjam' => 'required|integer|min:1|max:365',
            'maksimalBuku' => 'required|integer|min:1|max:20',
        ]);

        Setting::set('nama_perpus', $this->namaPerpus);
        Setting::set('lama_pinjam', $this->lamaPinjam);
        Setting::set('maksimal_buku', $this->maksimalBuku);

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            Setting::set('logo', $path);
            $this->logoPath = $path;
            $this->logo = null;
        }

        session()->flash('success', 'Pengaturan berhasil disimpan.');
    }

    public function render()
    {
        return view('components.setting.setting');
    }
};
