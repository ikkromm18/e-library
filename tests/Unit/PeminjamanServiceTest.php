<?php

namespace Tests\Unit;

use App\Exceptions\AnggotaNonaktifException;
use App\Exceptions\AnggotaTerlambatException;
use App\Exceptions\MelebihiLimitException;
use App\Exceptions\StokHabisException;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\Setting;
use App\Models\User;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanServiceTest extends TestCase
{
    use RefreshDatabase;

    private PeminjamanService $service;

    private User $petugas;

    private Anggota $anggota;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PeminjamanService;
        $this->petugas = User::factory()->petugas()->create();
        $this->anggota = Anggota::factory()->create();
    }

    public function test_sukses_membuat_peminjaman(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 3]);
        Setting::set('lama_pinjam', 7);
        Setting::set('maksimal_buku', 3);

        $peminjaman = $this->service->buatPeminjaman($this->anggota->id, [$buku->id], $this->petugas->id);

        $this->assertInstanceOf(Peminjaman::class, $peminjaman);
        $this->assertMatchesRegularExpression('/^PJM-\d{8}-\d+$/', $peminjaman->no_transaksi);
        $this->assertEquals('dipinjam', $peminjaman->status);
        $this->assertEquals(today(), $peminjaman->tanggal);
        $this->assertEquals(today()->addDays(7), $peminjaman->tanggal_jatuh_tempo);
        $this->assertEquals($this->petugas->id, $peminjaman->petugas_id);
        $this->assertEquals(1, $peminjaman->details()->count());
        $this->assertEquals(2, $buku->fresh()->stokTersedia());
    }

    public function test_anggota_nonaktif_ditolak(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 3]);
        $this->anggota->update(['status' => 'nonaktif']);

        $this->expectException(AnggotaNonaktifException::class);
        $this->service->buatPeminjaman($this->anggota->id, [$buku->id], $this->petugas->id);
    }

    public function test_anggota_terlambat_ditolak(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 3]);
        $peminjaman = Peminjaman::factory()->create([
            'anggota_id' => $this->anggota->id,
            'tanggal_jatuh_tempo' => today()->subDays(2),
        ]);
        PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku->id]);

        $this->expectException(AnggotaTerlambatException::class);
        $this->service->buatPeminjaman($this->anggota->id, [$buku->id], $this->petugas->id);
    }

    public function test_melebihi_limit_ditolak(): void
    {
        Setting::set('maksimal_buku', 2);
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku2 = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku3 = Buku::factory()->create(['jumlah_eksemplar' => 5]);

        $this->expectException(MelebihiLimitException::class);
        $this->service->buatPeminjaman($this->anggota->id, [$buku->id, $buku2->id, $buku3->id], $this->petugas->id);
    }

    public function test_cart_kosong_ditolak(): void
    {
        $this->expectException(MelebihiLimitException::class);
        $this->service->buatPeminjaman($this->anggota->id, [], $this->petugas->id);
    }

    public function test_stok_habis_ditolak(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 1]);
        $p = Peminjaman::factory()->create(['anggota_id' => $this->anggota->id]);
        PeminjamanDetail::create(['peminjaman_id' => $p->id, 'buku_id' => $buku->id]);

        try {
            $this->service->buatPeminjaman($this->anggota->id, [$buku->id], $this->petugas->id);
            $this->fail('Seharusnya throw StokHabisException');
        } catch (StokHabisException $e) {
            $this->assertEquals($buku->id, $e->buku->id);
        }
    }
}
