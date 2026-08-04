<?php

namespace Tests\Feature\Livewire;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KategoriTest extends TestCase
{
    use RefreshDatabase;

    public function test_buat_kategori(): void
    {
        Livewire::actingAs(User::factory()->petugas()->create())
            ->test(\Livewire\Livewire::new('kategori'))
            ->call('create')
            ->set('nama', 'Novel')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategoris', ['nama' => 'Novel']);
    }

    public function test_edit_kategori(): void
    {
        $kategori = Kategori::create(['nama' => 'Lama']);

        Livewire::actingAs(User::factory()->petugas()->create())
            ->test(\Livewire\Livewire::new('kategori'))
            ->call('edit', $kategori->id)
            ->set('nama', 'Baru')
            ->call('save');

        $kategori->refresh();
        $this->assertEquals('Baru', $kategori->nama);
    }

    public function test_hapus_kategori_dengan_buku_ditolak(): void
    {
        $kategori = Kategori::create(['nama' => 'Terisi']);
        Buku::factory()->create(['kategori_id' => $kategori->id]);

        Livewire::actingAs(User::factory()->petugas()->create())
            ->test(\Livewire\Livewire::new('kategori'))
            ->call('delete', $kategori->id);

        $this->assertDatabaseHas('kategoris', ['id' => $kategori->id]);
    }

    public function test_hapus_kategori_kosong_berhasil(): void
    {
        $kategori = Kategori::create(['nama' => 'Kosong']);

        Livewire::actingAs(User::factory()->petugas()->create())
            ->test(\Livewire\Livewire::new('kategori'))
            ->call('delete', $kategori->id);

        $this->assertDatabaseMissing('kategoris', ['id' => $kategori->id]);
    }

    public function test_nama_wajib(): void
    {
        Livewire::actingAs(User::factory()->petugas()->create())
            ->test(\Livewire\Livewire::new('kategori'))
            ->call('create')
            ->set('nama', '')
            ->call('save')
            ->assertHasErrors(['nama']);
    }
}
