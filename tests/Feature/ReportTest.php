<?php

namespace Tests\Feature;

use App\Exports\TransaksiExport;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
