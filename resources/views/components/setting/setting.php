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

    public $namaPetugas = '';

    public $jabatanPetugas = '';

    public function mount(): void
    {
        $this->namaPerpus = Setting::get('nama_perpus', 'Perpustakaan');
        $this->logoPath = Setting::get('logo', null);
        $this->lamaPinjam = (int) Setting::get('lama_pinjam', 7);
        $this->maksimalBuku = (int) Setting::get('maksimal_buku', 3);
        $this->namaPetugas = Setting::get('ttd_nama_petugas', auth()->user()?->name ?? '');
        $this->jabatanPetugas = Setting::get('ttd_jabatan_petugas', 'Petugas Perpustakaan');
    }

    public function simpan(): void
    {
        $this->validate([
            'namaPerpus' => 'required|string|max:255',
            'lamaPinjam' => 'required|integer|min:1|max:365',
            'maksimalBuku' => 'required|integer|min:1|max:20',
            'logo' => 'nullable|mimes:jpeg,png,jpg,gif,webp,svg|max:1024',
            'namaPetugas' => 'required|string|max:255',
            'jabatanPetugas' => 'required|string|max:255',
        ]);

        Setting::set('nama_perpus', $this->namaPerpus);
        Setting::set('lama_pinjam', $this->lamaPinjam);
        Setting::set('maksimal_buku', $this->maksimalBuku);
        Setting::set('ttd_nama_petugas', $this->namaPetugas);
        Setting::set('ttd_jabatan_petugas', $this->jabatanPetugas);

        if ($this->logo) {
            $filename = $this->logo->store('', 'upload');
            $path = 'upload/'.$filename;
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
