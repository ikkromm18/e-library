<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Setting;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function print(Peminjaman $peminjaman): View
    {
        $peminjaman->load(['anggota', 'petugas', 'details.buku']);

        $namaPerpus = Setting::get('nama_perpus', 'Perpustakaan');
        $logoPath = Setting::get('logo', null);
        $ttdNama = Setting::get('ttd_nama_petugas', $peminjaman->petugas->name ?? 'Petugas Perpustakaan');
        $ttdJabatan = Setting::get('ttd_jabatan_petugas', 'Petugas Perpustakaan');

        return view('reports.bukti-peminjaman', [
            'peminjaman' => $peminjaman,
            'namaPerpus' => $namaPerpus,
            'logoPath' => $logoPath,
            'ttdNama' => $ttdNama,
            'ttdJabatan' => $ttdJabatan,
        ]);
    }
}
