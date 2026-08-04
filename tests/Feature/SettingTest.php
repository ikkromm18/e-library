<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_dan_get(): void
    {
        Setting::set('lama_pinjam', 7);

        $this->assertEquals('7', Setting::get('lama_pinjam'));
        $this->assertEquals('default', Setting::get('tidak_ada', 'default'));
        $this->assertTrue(Cache::has('setting.lama_pinjam'));
    }

    public function test_set_update_menimpa_nilai_lama(): void
    {
        Setting::set('maksimal_buku', 3);
        Setting::set('maksimal_buku', 5);

        $this->assertEquals('5', Setting::get('maksimal_buku'));
        $this->assertSame(1, Setting::count());
    }

    public function test_set_membersihkan_cache(): void
    {
        Setting::set('lama_pinjam', 7);
        $this->assertEquals('7', Setting::get('lama_pinjam'));

        Setting::set('lama_pinjam', 14);
        $this->assertEquals('14', Setting::get('lama_pinjam'));
    }
}
