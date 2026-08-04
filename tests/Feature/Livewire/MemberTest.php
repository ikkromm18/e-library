<?php

namespace Tests\Feature\Livewire;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
    }

    public function test_buat_anggota(): void
    {
        Livewire::actingAs($this->user)
            ->test('anggota')
            ->call('create')
            ->set('nis', '20240001')
            ->set('nama', 'Ahmad Fauzi')
            ->set('jenis_kelamin', 'L')
            ->set('kelas', '7A')
            ->set('tanggal_masuk', '2024-07-01')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('anggota', ['nis' => '20240001', 'nama' => 'Ahmad Fauzi']);
    }

    public function test_nis_duplikat_ditolak(): void
    {
        Anggota::factory()->create(['nis' => '20240001']);

        Livewire::actingAs($this->user)
            ->test('anggota')
            ->call('create')
            ->set('nis', '20240001')
            ->set('nama', 'Duplikat')
            ->set('jenis_kelamin', 'L')
            ->set('tanggal_masuk', '2024-07-01')
            ->call('save')
            ->assertHasErrors(['nis']);
    }

    public function test_search_anggota(): void
    {
        Anggota::create([
            'nis' => '20240001', 'nama' => 'Ahmad Fauzi', 'jenis_kelamin' => 'L',
            'kelas' => '7A', 'tanggal_masuk' => '2024-07-01', 'status' => 'aktif',
        ]);
        Anggota::create([
            'nis' => '20240002', 'nama' => 'Siti Rahmawati', 'jenis_kelamin' => 'P',
            'kelas' => '8B', 'tanggal_masuk' => '2024-07-01', 'status' => 'aktif',
        ]);

        Livewire::actingAs($this->user)
            ->test('anggota')
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Siti Rahmawati');

        Livewire::actingAs($this->user)
            ->test('anggota')
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Fauzi')
            ->assertDontSee('Siti Rahmawati');
    }

    public function test_filter_status(): void
    {
        Anggota::create([
            'nis' => '20240001', 'nama' => 'Ahmad Aktif', 'jenis_kelamin' => 'L',
            'tanggal_masuk' => '2024-07-01', 'status' => 'aktif',
        ]);
        Anggota::create([
            'nis' => '20240002', 'nama' => 'Budi Lulus', 'jenis_kelamin' => 'L',
            'tanggal_masuk' => '2023-07-01', 'status' => 'lulus',
        ]);

        Livewire::actingAs($this->user)
            ->test('anggota')
            ->set('filterStatus', 'aktif')
            ->assertSee('Ahmad Aktif')
            ->assertDontSee('Budi Lulus');
    }

    public function test_nis_wajib(): void
    {
        Livewire::actingAs($this->user)
            ->test('anggota')
            ->call('create')
            ->set('nis', '')
            ->set('nama', 'Test')
            ->set('jenis_kelamin', 'L')
            ->set('tanggal_masuk', '2024-07-01')
            ->call('save')
            ->assertHasErrors(['nis']);
    }

    public function test_nama_wajib(): void
    {
        Livewire::actingAs($this->user)
            ->test('anggota')
            ->call('create')
            ->set('nis', '20240099')
            ->set('nama', '')
            ->set('jenis_kelamin', 'L')
            ->set('tanggal_masuk', '2024-07-01')
            ->call('save')
            ->assertHasErrors(['nama']);
    }

    public function test_edit_anggota(): void
    {
        $anggota = Anggota::factory()->create(['nama' => 'Nama Lama']);

        Livewire::actingAs($this->user)
            ->test('anggota')
            ->call('edit', $anggota->id)
            ->set('nama', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('anggota', ['id' => $anggota->id, 'nama' => 'Nama Baru']);
    }
}
