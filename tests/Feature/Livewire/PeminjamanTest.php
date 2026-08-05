<?php

namespace Tests\Feature\Livewire;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Rak;
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
