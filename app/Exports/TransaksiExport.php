<?php

namespace App\Exports;

use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransaksiExport implements FromCollection, WithHeadings
{
    public function __construct(private string $jenis = 'peminjaman') {}

    public function collection(): Collection
    {
        if ($this->jenis === 'pengembalian') {
            return PeminjamanDetail::with(['peminjaman.anggota', 'buku'])
                ->whereNotNull('tanggal_kembali')
                ->orderByDesc('tanggal_kembali')
                ->get()
                ->map(fn (PeminjamanDetail $d) => [
                    $d->peminjaman->no_transaksi,
                    $d->tanggal_kembali->format('d/m/Y'),
                    $d->buku->judul,
                    $d->peminjaman->anggota->nama,
                ]);
        }

        if ($this->jenis === 'terlambat') {
            return PeminjamanDetail::with(['peminjaman.anggota', 'buku'])
                ->whereNotNull('keterlambatan_hari')
                ->where('keterlambatan_hari', '>', 0)
                ->orderByDesc('keterlambatan_hari')
                ->get()
                ->map(fn (PeminjamanDetail $d) => [
                    $d->peminjaman->no_transaksi,
                    $d->peminjaman->anggota->nama,
                    $d->buku->judul,
                    $d->peminjaman->tanggal_jatuh_tempo->format('d/m/Y'),
                    $d->keterlambatan_hari,
                ]);
        }

        return Peminjaman::with(['anggota', 'petugas'])
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn (Peminjaman $p) => [
                $p->no_transaksi,
                $p->tanggal->format('d/m/Y'),
                $p->anggota->nama,
                $p->petugas->name,
                $p->details()->count(),
                $p->status,
            ]);
    }

    public function headings(): array
    {
        return match ($this->jenis) {
            'pengembalian' => ['No Transaksi', 'Tanggal Kembali', 'Buku', 'Anggota'],
            'terlambat' => ['No Transaksi', 'Anggota', 'Buku', 'Jatuh Tempo', 'Terlambat (Hari)'],
            default => ['No Transaksi', 'Tanggal', 'Anggota', 'Petugas', 'Jumlah Buku', 'Status'],
        };
    }
}
