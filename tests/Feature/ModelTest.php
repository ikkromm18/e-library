<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_stok_tersedia_dihitung_dari_pinjaman_aktif(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 3]);
        $this->assertEquals(3, $buku->stokTersedia());

        $anggota = Anggota::factory()->create(['status' => 'aktif']);
        $peminjaman = Peminjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'status' => 'dipinjam',
        ]);
        PeminjamanDetail::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id' => $buku->id,
        ]);

        $buku->refresh();
        $this->assertEquals(2, $buku->stokTersedia());
    }

    public function test_stok_tersedia_ignores_returned_items(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $anggota = Anggota::factory()->create(['status' => 'aktif']);
        $peminjaman = Peminjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'status' => 'selesai',
        ]);
        PeminjamanDetail::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id' => $buku->id,
            'tanggal_kembali' => now()->subDay(),
        ]);

        $buku->refresh();
        $this->assertEquals(2, $buku->stokTersedia());
    }

    public function test_buat_kode_otomatis(): void
    {
        $kode = Buku::buatKode();
        $this->assertMatchesRegularExpression('/^BUK-\d{4}$/', $kode);
        $this->assertEquals('BUK-0001', $kode);
    }

    public function test_buat_kode_skip_existing(): void
    {
        Buku::factory()->create(['kode' => 'BUK-0001']);
        $kode = Buku::buatKode();
        $this->assertEquals('BUK-0002', $kode);
    }

    public function test_generate_no_transaksi_otomatis(): void
    {
        $no = Peminjaman::generateNoTransaksi();
        $expected = 'PJM-'.Carbon::now()->format('Ymd').'-1';
        $this->assertEquals($expected, $no);
    }

    public function test_generate_no_transaksi_increment(): void
    {
        Peminjaman::factory()->create(['no_transaksi' => 'PJM-'.Carbon::now()->format('Ymd').'-1']);
        $no = Peminjaman::generateNoTransaksi();
        $expected = 'PJM-'.Carbon::now()->format('Ymd').'-2';
        $this->assertEquals($expected, $no);
    }

    public function test_hitung_keterlambatan_saat_tepat_waktu(): void
    {
        $p = Peminjaman::factory()->create([
            'tanggal_jatuh_tempo' => Carbon::tomorrow(),
        ]);
        $this->assertEquals(0, $p->hitungKeterlambatan());
    }

    public function test_hitung_keterlambatan_saat_terlambat(): void
    {
        $p = Peminjaman::factory()->create([
            'tanggal_jatuh_tempo' => Carbon::yesterday(),
        ]);
        $this->assertGreaterThanOrEqual(1, $p->hitungKeterlambatan());
    }

    public function test_user_admin_role(): void
    {
        $user = User::factory()->admin()->create();
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isPetugas());
    }

    public function test_user_petugas_role(): void
    {
        $user = User::factory()->petugas()->create();
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isPetugas());
    }
}
