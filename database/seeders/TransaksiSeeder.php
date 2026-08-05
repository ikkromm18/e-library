<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $petugas = User::where('role', 'petugas')->pluck('id')->all();
        $anggota = Anggota::where('status', 'aktif')->pluck('id')->all();
        $bukuList = Buku::where('status', 'aktif')->get();
        $bukuIds = $bukuList->pluck('id')->all();

        foreach (range(1, 30) as $i) {
            $peminjaman = Peminjaman::create([
                'no_transaksi' => Peminjaman::generateNoTransaksi(),
                'tanggal' => today()->subDays(rand(0, 20)),
                'tanggal_jatuh_tempo' => today()->subDays(rand(-10, 10)),
                'petugas_id' => $petugas[array_rand($petugas)],
                'anggota_id' => $anggota[array_rand($anggota)],
                'status' => 'dipinjam',
            ]);

            $jumlah = rand(1, 3);
            $dipilih = array_rand($bukuIds, min($jumlah, count($bukuIds)));

            foreach ((array) $dipilih as $key) {
                $sudahKembali = $peminjaman->tanggal_jatuh_tempo->lt(today()) ? (bool) rand(0, 1) : false;
                PeminjamanDetail::create([
                    'peminjaman_id' => $peminjaman->id,
                    'buku_id' => $bukuIds[$key],
                    'tanggal_kembali' => $sudahKembali ? today() : null,
                    'keterlambatan_hari' => $sudahKembali && $peminjaman->tanggal_jatuh_tempo->lt(today())
                        ? today()->diffInDays($peminjaman->tanggal_jatuh_tempo)
                        : null,
                ]);
            }

            if ($peminjaman->sudahSelesai()) {
                $peminjaman->update(['status' => 'selesai']);
            }
        }
    }
}
