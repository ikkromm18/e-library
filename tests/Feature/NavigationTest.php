<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_see_standard_navigation_links(): void
    {
        $user = User::factory()->petugas()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Katalog');
        $response->assertSee('Buku');
        $response->assertSee('Kategori');
        $response->assertSee('Rak');
        $response->assertSee('Sirkulasi');
        $response->assertSee('Peminjaman');
        $response->assertSee('Peminjam per Siswa');
        $response->assertSee('Pengembalian');
        $response->assertSee('Anggota');
        $response->assertSee('Laporan');
        $response->assertDontSee('Setting');
    }

    public function test_admin_can_see_system_settings_links(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sistem');
        $response->assertSee('Pengguna');
        $response->assertSee('Setting');
    }
}
