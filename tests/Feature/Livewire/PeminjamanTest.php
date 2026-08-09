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

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Anggota $anggota;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
        $this->anggota = Anggota::factory()->create();
    }

    public function test_alur_pilih_anggota_tambah_buku_simpan(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);

        Livewire::actingAs($this->user)
            ->test('peminjaman')
            ->set('searchAnggota', $this->anggota->nama)
            ->call('pilihAnggota', $this->anggota->id)
            ->assertSet('anggotaDipilih.id', $this->anggota->id)
            ->set('searchBuku', $buku->judul)
            ->call('tambahBuku', $buku->id)
            ->assertSet('cart.0.id', $buku->id)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('peminjamen', ['anggota_id' => $this->anggota->id, 'status' => 'dipinjam']);
        $this->assertEquals(1, $buku->fresh()->stokTersedia());
    }

    public function test_cetak_bukti_peminjaman_route(): void
    {
        $buku = Buku::factory()->create();
        $peminjaman = Peminjaman::create([
            'no_transaksi' => 'PJM-TEST-001',
            'tanggal' => today(),
            'tanggal_jatuh_tempo' => today()->addDays(7),
            'petugas_id' => $this->user->id,
            'anggota_id' => $this->anggota->id,
            'status' => 'dipinjam',
        ]);
        PeminjamanDetail::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id' => $buku->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('peminjaman.print', $peminjaman->id));

        $response->assertStatus(200)
            ->assertSee('PJM-TEST-001')
            ->assertSee($this->anggota->nama)
            ->assertSee($buku->judul);
    }

    public function test_cart_cegah_duplikat_buku(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 5]);

        $component = Livewire::actingAs($this->user)->test('peminjaman');
        $component->call('pilihAnggota', $this->anggota->id);
        $component->call('tambahBuku', $buku->id);
        $component->call('tambahBuku', $buku->id);

        $this->assertCount(1, $component->get('cart'));
    }

    public function test_anggota_nonaktif_ditolak(): void
    {
        $this->anggota->update(['status' => 'nonaktif']);
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);

        Livewire::actingAs($this->user)
            ->test('peminjaman')
            ->call('pilihAnggota', $this->anggota->id)
            ->call('tambahBuku', $buku->id)
            ->call('simpan');

        $this->assertDatabaseMissing('peminjamen', ['anggota_id' => $this->anggota->id]);
        $this->assertEquals(2, $buku->fresh()->stokTersedia());
    }

    public function test_cart_limit_ditolak(): void
    {
        $buku1 = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku2 = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku3 = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku4 = Buku::factory()->create(['jumlah_eksemplar' => 5]);

        $component = Livewire::actingAs($this->user)->test('peminjaman');
        $component->call('pilihAnggota', $this->anggota->id);
        foreach ([$buku1, $buku2, $buku3, $buku4] as $b) {
            $component->call('tambahBuku', $b->id);
        }

        $this->assertCount(3, $component->get('cart'));
    }
}
