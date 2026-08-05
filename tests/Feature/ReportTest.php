<?php

namespace Tests\Feature;

use App\Exports\AnggotaExport;
use App\Exports\TransaksiExport;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
    }

    public function test_export_pdf_buku(): void
    {
        Buku::factory()->create();
        Buku::factory()->create();

        $response = $this->actingAs($this->user)->get('/laporan/export/buku/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_export_excel_anggota(): void
    {
        Anggota::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get('/laporan/export/anggota/excel');

        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    public function test_laporan_transaksi_terlambat(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $peminjaman = Peminjaman::factory()->create(['tanggal_jatuh_tempo' => today()->subDays(3)]);
        PeminjamanDetail::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id' => $buku->id,
            'tanggal_kembali' => today(),
            'keterlambatan_hari' => 3,
        ]);

        $rows = (new TransaksiExport('terlambat'))->collection();

        $this->assertCount(1, $rows);
        $this->assertEquals($peminjaman->no_transaksi, $rows->first()[0]);
    }

    public function test_format_tidak_dikenal_404(): void
    {
        $response = $this->actingAs($this->user)->get('/laporan/export/buku/unknown');
        $response->assertNotFound();
    }

    public function test_pagination_link_points_to_laporan(): void
    {
        Anggota::factory()->count(25)->create();

        Livewire::actingAs($this->user)
            ->test('laporan')
            ->set('tipe', 'anggota')
            ->assertSeeHtml('/laporan?page=2');
    }

    public function test_anggota_export_filters_by_date_range(): void
    {
        Anggota::factory()->create(['tanggal_masuk' => '2024-01-15']);
        Anggota::factory()->create(['tanggal_masuk' => '2024-06-20']);
        Anggota::factory()->create(['tanggal_masuk' => '2025-01-10']);

        $rows = (new AnggotaExport(null, '2024-01-01', '2024-12-31'))->collection();

        $this->assertCount(2, $rows);
    }

    public function test_anggota_export_no_filter_when_empty_range(): void
    {
        Anggota::factory()->count(3)->create();

        $rows = (new AnggotaExport)->collection();

        $this->assertCount(3, $rows);
    }

    public function test_transaksi_peminjaman_export_filters_by_date_range(): void
    {
        $p1 = Peminjaman::factory()->create(['tanggal' => '2024-03-10']);
        $p2 = Peminjaman::factory()->create(['tanggal' => '2024-09-15']);
        $p3 = Peminjaman::factory()->create(['tanggal' => '2025-02-01']);

        $rows = (new TransaksiExport('peminjaman', '2024-01-01', '2024-12-31'))->collection();

        $this->assertCount(2, $rows);
        $nos = $rows->pluck(0)->toArray();
        $this->assertContains($p1->no_transaksi, $nos);
        $this->assertContains($p2->no_transaksi, $nos);
    }

    public function test_transaksi_pengembalian_export_filters_by_date_range(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $p1 = Peminjaman::factory()->create();
        PeminjamanDetail::create(['peminjaman_id' => $p1->id, 'buku_id' => $buku->id, 'tanggal_kembali' => '2024-05-10']);
        $p2 = Peminjaman::factory()->create();
        PeminjamanDetail::create(['peminjaman_id' => $p2->id, 'buku_id' => $buku->id, 'tanggal_kembali' => '2025-01-20']);

        $rows = (new TransaksiExport('pengembalian', '2024-01-01', '2024-12-31'))->collection();

        $this->assertCount(1, $rows);
    }

    public function test_export_route_passes_date_params(): void
    {
        Anggota::factory()->create(['tanggal_masuk' => '2024-06-01']);
        Anggota::factory()->create(['tanggal_masuk' => '2025-03-01']);

        $response = $this->actingAs($this->user)->get('/laporan/export/anggota/excel?dari=2024-01-01&sampai=2024-12-31');

        $response->assertOk();
    }
}
