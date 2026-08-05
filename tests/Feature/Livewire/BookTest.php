<?php

namespace Tests\Feature\Livewire;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\Rak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
        Kategori::create(['nama' => 'Novel']);
        Rak::create(['kode' => 'A-01', 'nama' => 'Rak A']);
    }

    public function test_kode_buku_otomatis(): void
    {
        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('create')
            ->assertSet('kode', 'BUK-0001');
    }

    public function test_buat_buku(): void
    {
        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('create')
            ->set('judul', 'Laskar Pelangi')
            ->set('kategori_id', Kategori::first()->id)
            ->set('rak_id', Rak::first()->id)
            ->set('jumlah_eksemplar', 3)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bukus', ['judul' => 'Laskar Pelangi']);
    }

    public function test_judul_wajib(): void
    {
        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('create')
            ->set('judul', '')
            ->set('kategori_id', Kategori::first()->id)
            ->set('rak_id', Rak::first()->id)
            ->call('save')
            ->assertHasErrors(['judul']);
    }

    public function test_kategori_wajib(): void
    {
        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('create')
            ->set('judul', 'Test')
            ->set('kategori_id', '')
            ->set('rak_id', Rak::first()->id)
            ->call('save')
            ->assertHasErrors(['kategori_id']);
    }

    public function test_rak_wajib(): void
    {
        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('create')
            ->set('judul', 'Test')
            ->set('kategori_id', Kategori::first()->id)
            ->set('rak_id', '')
            ->call('save')
            ->assertHasErrors(['rak_id']);
    }

    public function test_isbn_duplikat_ditolak(): void
    {
        Buku::factory()->create(['isbn' => '978-3-16-148410-0']);

        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('create')
            ->set('isbn', '978-3-16-148410-0')
            ->set('judul', 'Test Book')
            ->set('kategori_id', Kategori::first()->id)
            ->set('rak_id', Rak::first()->id)
            ->call('save')
            ->assertHasErrors(['isbn']);
    }

    public function test_search_buku(): void
    {
        $rak = Rak::first();
        $kat = Kategori::first();
        Buku::create([
            'kode' => 'BUK-0001', 'judul' => 'Buku Test Unik', 'kategori_id' => $kat->id, 'rak_id' => $rak->id, 'jumlah_eksemplar' => 1,
        ]);
        Buku::create([
            'kode' => 'BUK-0002', 'judul' => 'Buku Lain', 'kategori_id' => $kat->id, 'rak_id' => $rak->id, 'jumlah_eksemplar' => 1,
        ]);

        $this->assertEquals(2, Buku::count(), 'Buku records should exist');

        $component = Livewire::actingAs($this->user)
            ->test('buku');

        $bukusInComponent = Buku::with(['kategori', 'rak'])->orderBy('kode')->get();
        $this->assertEquals(2, $bukusInComponent->count(), 'Direct query should find 2 bukus');

        $component->assertSee('Buku Test Unik')
            ->assertSee('Buku Lain');
    }

    public function test_filter_kategori(): void
    {
        $kat1 = Kategori::create(['nama' => 'Agama']);
        $kat2 = Kategori::create(['nama' => 'Komik']);
        $rak = Rak::first();
        Buku::create([
            'kode' => 'BUK-0003', 'judul' => 'Buku Agama', 'kategori_id' => $kat1->id, 'rak_id' => $rak->id, 'jumlah_eksemplar' => 1,
        ]);
        Buku::create([
            'kode' => 'BUK-0004', 'judul' => 'Buku Komik', 'kategori_id' => $kat2->id, 'rak_id' => $rak->id, 'jumlah_eksemplar' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test('buku')
            ->set('filterKategori', $kat1->id)
            ->assertSee('Buku Agama')
            ->assertDontSee('Buku Komik');
    }

    public function test_detail_buku(): void
    {
        $buku = Buku::factory()->create([
            'judul' => 'Detail Book',
            'kategori_id' => Kategori::first()->id,
            'rak_id' => Rak::first()->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('detail', $buku->id)
            ->assertSet('showDetail', true)
            ->assertSet('detailBuku.judul', 'Detail Book');
    }

    public function test_delete_buku_dengan_riwayat_ditolak(): void
    {
        $buku = Buku::factory()->create([
            'kategori_id' => Kategori::first()->id,
            'rak_id' => Rak::first()->id,
        ]);
        $peminjaman = Peminjaman::factory()->selesai()->create();
        PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku->id]);

        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('delete', $buku->id);

        $this->assertDatabaseHas('bukus', ['id' => $buku->id]);
    }

    public function test_edit_buku_stok_tidak_bisa_kurang_dari_dipinjam(): void
    {
        $buku = Buku::factory()->create([
            'kategori_id' => Kategori::first()->id,
            'rak_id' => Rak::first()->id,
            'jumlah_eksemplar' => 3,
        ]);
        $peminjaman1 = Peminjaman::factory()->create();
        $peminjaman2 = Peminjaman::factory()->create();
        PeminjamanDetail::create(['peminjaman_id' => $peminjaman1->id, 'buku_id' => $buku->id]);
        PeminjamanDetail::create(['peminjaman_id' => $peminjaman2->id, 'buku_id' => $buku->id]);

        Livewire::actingAs($this->user)
            ->test('buku')
            ->call('edit', $buku->id)
            ->set('jumlah_eksemplar', 1)
            ->call('save');

        $this->assertDatabaseHas('bukus', ['id' => $buku->id, 'jumlah_eksemplar' => 3]);
    }
}
