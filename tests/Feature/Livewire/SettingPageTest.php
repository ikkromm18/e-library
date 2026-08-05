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
            ->call('simpan');

        $this->assertEquals('Perpustakaan SMP Nusantara', Setting::get('nama_perpus'));
        $this->assertEquals('14', Setting::get('lama_pinjam'));
        $this->assertEquals('5', Setting::get('maksimal_buku'));
    }

    public function test_upload_logo(): void
    {
        Storage::fake('public');
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
        Storage::disk('public')->assertExists(Setting::get('logo'));
    }
}
