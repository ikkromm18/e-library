<?php

namespace App\Services;

use App\Exceptions\AnggotaNonaktifException;
use App\Exceptions\AnggotaTerlambatException;
use App\Exceptions\MelebihiLimitException;
use App\Exceptions\StokHabisException;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PeminjamanService
{
    public function buatPeminjaman(int $anggotaId, array $bukuIds, int $petugasId): Peminjaman
    {
        $anggota = Anggota::findOrFail($anggotaId);

        if ($anggota->status !== 'aktif') {
            throw new AnggotaNonaktifException;
        }

        if ($this->punyaBukuTerlambat($anggotaId)) {
            throw new AnggotaTerlambatException;
        }

        $maksimal = (int) Setting::get('maksimal_buku', 3);
        if (count($bukuIds) < 1 || count($bukuIds) > $maksimal) {
            throw new MelebihiLimitException;
        }

        foreach (Buku::whereIn('id', $bukuIds)->get() as $buku) {
            if ($buku->stokTersedia() < 1) {
                throw new StokHabisException($buku);
            }
        }

        $lama = (int) Setting::get('lama_pinjam', 7);

        return DB::transaction(function () use ($anggotaId, $bukuIds, $petugasId, $lama) {
            $peminjaman = Peminjaman::create([
                'no_transaksi' => Peminjaman::generateNoTransaksi(),
                'tanggal' => today(),
                'tanggal_jatuh_tempo' => today()->addDays($lama),
                'petugas_id' => $petugasId,
                'anggota_id' => $anggotaId,
                'status' => 'dipinjam',
            ]);

            foreach ($bukuIds as $bukuId) {
                PeminjamanDetail::create([
                    'peminjaman_id' => $peminjaman->id,
                    'buku_id' => $bukuId,
                ]);
            }

            return $peminjaman;
        });
    }

    private function punyaBukuTerlambat(int $anggotaId): bool
    {
        return PeminjamanDetail::whereNull('tanggal_kembali')
            ->whereHas('peminjaman', function ($q) use ($anggotaId) {
                $q->where('status', 'dipinjam')
                    ->where('anggota_id', $anggotaId)
                    ->whereDate('tanggal_jatuh_tempo', '<', today());
            })
            ->exists();
    }
}
