<?php

namespace Tests\Feature\Livewire;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PengembalianTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
    }

    public function test_kembalikan_tepat_waktu(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $peminjaman = Peminjaman::factory()->create(['tanggal_jatuh_tempo' => today()->addDays(3)]);
        $detail = PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku->id]);

        Livewire::actingAs($this->user)
            ->test('pengembalian')
            ->call('pilih', $peminjaman->id)
            ->assertSet('peminjaman.id', $peminjaman->id)
            ->call('kembalikan', $detail->id);

        $detail->refresh();
        $this->assertNotNull($detail->tanggal_kembali);
        $this->assertNull($detail->keterlambatan_hari);
        $this->assertEquals(2, $buku->fresh()->stokTersedia());
    }

    public function test_kembalikan_terlambat(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $peminjaman = Peminjaman::factory()->create(['tanggal_jatuh_tempo' => today()->subDays(5)]);
        $detail = PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku->id]);

        Livewire::actingAs($this->user)
            ->test('pengembalian')
            ->call('pilih', $peminjaman->id)
            ->call('kembalikan', $detail->id);

        $detail->refresh();
        $this->assertEquals(5, $detail->keterlambatan_hari);
    }

    public function test_header_selesai_saat_semua_kembali(): void
    {
        $buku1 = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $buku2 = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $peminjaman = Peminjaman::factory()->create();
        $d1 = PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku1->id]);
        $d2 = PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku2->id]);

        Livewire::actingAs($this->user)
            ->test('pengembalian')
            ->call('pilih', $peminjaman->id)
            ->call('kembalikan', $d1->id)
            ->call('kembalikan', $d2->id);

        $this->assertEquals('selesai', $peminjaman->fresh()->status);
    }

    public function test_cari_transaksi_via_no_transaksi(): void
    {
        $peminjaman = Peminjaman::factory()->create(['no_transaksi' => 'PJM-20260805-1']);

        Livewire::actingAs($this->user)
            ->test('pengembalian')
            ->set('search', '20260805')
            ->assertSet('hasilTransaksi.0.id', $peminjaman->id);
    }
}
