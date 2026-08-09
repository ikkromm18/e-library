<?php

namespace Tests\Feature\Livewire;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Storage;
use Tests\TestCase;

class SettingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_simpan_pengaturan(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test('setting')
            ->set('namaPerpus', 'Perpustakaan SMP Nusantara')
            ->set('lamaPinjam', 14)
            ->set('maksimalBuku', 5)
            ->set('namaPetugas', 'Ahmad Petugas')
            ->set('jabatanPetugas', 'Kepala Perpus')
            ->call('simpan');

        $this->assertEquals('Perpustakaan SMP Nusantara', Setting::get('nama_perpus'));
        $this->assertEquals('14', Setting::get('lama_pinjam'));
        $this->assertEquals('5', Setting::get('maksimal_buku'));
        $this->assertEquals('Ahmad Petugas', Setting::get('ttd_nama_petugas'));
        $this->assertEquals('Kepala Perpus', Setting::get('ttd_jabatan_petugas'));
    }

    public function test_upload_logo(): void
    {
        Storage::fake('upload');
        $fake = UploadedFile::fake()->image('logo.png');
        $file = new NamedUploadedFile(
            $fake->getRealPath(),
            'logo.png',
            'image/png',
            null,
            true
        );

        Livewire::actingAs(User::factory()->admin()->create())
            ->test('setting')
            ->set('logo', $file)
            ->call('simpan');

        $this->assertNotNull(Setting::get('logo'));
        $filename = str_replace('upload/', '', Setting::get('logo'));
        Storage::disk('upload')->assertExists($filename);
    }
}
