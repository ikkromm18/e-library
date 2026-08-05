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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_dan_grafik(): void
    {
        Buku::factory()->create(['jumlah_eksemplar' => 2]);
        Buku::factory()->create(['jumlah_eksemplar' => 2]);
        Buku::factory()->create(['jumlah_eksemplar' => 2]);
        Anggota::factory()->create();
        Anggota::factory()->create();

        // aktif (sedang dipinjam)
        $aktif = Peminjaman::factory()->create(['tanggal' => today()]);
        $bukuA = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $bukuB = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        PeminjamanDetail::create(['peminjaman_id' => $aktif->id, 'buku_id' => $bukuA->id]);
        PeminjamanDetail::create(['peminjaman_id' => $aktif->id, 'buku_id' => $bukuB->id]);

        // terlambat
        $telat = Peminjaman::factory()->create(['tanggal' => today(), 'tanggal_jatuh_tempo' => today()->subDays(2)]);
        $bukuC = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        PeminjamanDetail::create(['peminjaman_id' => $telat->id, 'buku_id' => $bukuC->id]);

        // kembali hari ini
        $selesai = Peminjaman::factory()->selesai()->create(['tanggal' => today()->subDays(3)]);
        $bukuD = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        PeminjamanDetail::create([
            'peminjaman_id' => $selesai->id,
            'buku_id' => $bukuD->id,
            'tanggal_kembali' => today(),
            'keterlambatan_hari' => null,
        ]);

        Livewire::actingAs(User::factory()->petugas()->create())
            ->test('dashboard')
            ->assertSet('totalEksemplar', 14)
            ->assertSet('totalJudul', 7)
            ->assertSet('totalAnggota', 5)
            ->assertSet('sedangDipinjam', 3)
            ->assertSet('terlambat', 1)
            ->assertSet('kembaliHariIni', 1)
            ->assertSet('grafik.'.today()->format('Y-m-d'), 2);
    }
}
