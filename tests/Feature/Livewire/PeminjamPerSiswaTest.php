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

class PeminjamPerSiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_peminjam_per_siswa_dapat_diakses_oleh_user_login()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('peminjaman.per-siswa'))
            ->assertStatus(200);
    }

    public function test_komponen_dapat_menampilkan_siswa_dan_riwayat_peminjaman()
    {
        $user = User::factory()->create();
        $anggota = Anggota::factory()->create(['nama' => 'Budi Santoso', 'nis' => '12345']);
        $buku = Buku::factory()->create(['judul' => 'Belajar Laravel 13']);

        $peminjaman = Peminjaman::create([
            'no_transaksi' => 'PJM-20260809-1',
            'tanggal' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'petugas_id' => $user->id,
            'anggota_id' => $anggota->id,
            'status' => 'dipinjam',
        ]);

        PeminjamanDetail::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id' => $buku->id,
        ]);

        Livewire::actingAs($user)
            ->test('peminjam-per-siswa')
            ->assertSee('Budi Santoso')
            ->assertSee('12345')
            ->assertSee('Belajar Laravel 13');
    }

    public function test_filter_search_berfungsi_dengan_baik()
    {
        $user = User::factory()->create();
        $siswaA = Anggota::factory()->create(['nama' => 'Ahmad Rizki', 'nis' => '11111']);
        $siswaB = Anggota::factory()->create(['nama' => 'Siti Nurhaliza', 'nis' => '22222']);

        Peminjaman::create(['no_transaksi' => 'PJM-1', 'tanggal' => now(), 'tanggal_jatuh_tempo' => now()->addDays(7), 'petugas_id' => $user->id, 'anggota_id' => $siswaA->id, 'status' => 'dipinjam']);
        Peminjaman::create(['no_transaksi' => 'PJM-2', 'tanggal' => now(), 'tanggal_jatuh_tempo' => now()->addDays(7), 'petugas_id' => $user->id, 'anggota_id' => $siswaB->id, 'status' => 'dipinjam']);

        Livewire::actingAs($user)
            ->test('peminjam-per-siswa')
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Rizki')
            ->assertDontSee('Siti Nurhaliza');
    }
}
