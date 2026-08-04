<?php

namespace Tests\Feature\Livewire;

use App\Models\Buku;
use App\Models\Rak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RakTest extends TestCase
{
    use RefreshDatabase;

    public function test_buat_rak(): void
    {
        Livewire::actingAs(User::factory()->petugas()->create())
            ->test(\Livewire\Livewire::new('rak'))
            ->call('create')
            ->set('kode', 'A-01')
            ->set('nama', 'Rak A')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('raks', ['kode' => 'A-01']);
    }

    public function test_kode_duplikat_ditolak(): void
    {
        Rak::create(['kode' => 'A-01', 'nama' => 'Rak A']);

        Livewire::actingAs(User::factory()->petugas()->create())
            ->test(\Livewire\Livewire::new('rak'))
            ->call('create')
            ->set('kode', 'A-01')
            ->set('nama', 'Rak B')
            ->call('save')
            ->assertHasErrors(['kode']);
    }

    public function test_hapus_rak_dengan_buku_ditolak(): void
    {
        $rak = Rak::create(['kode' => 'A-01', 'nama' => 'Rak A']);
        Buku::factory()->create(['rak_id' => $rak->id]);

        Livewire::actingAs(User::factory()->petugas()->create())
            ->test(\Livewire\Livewire::new('rak'))
            ->call('delete', $rak->id);

        $this->assertDatabaseHas('raks', ['id' => $rak->id]);
    }
}
