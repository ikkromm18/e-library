<?php

namespace Tests\Feature\Livewire;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Rak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Storage;
use Tests\TestCase;

class BukuImportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
    }

    public function test_import_file_valid(): void
    {
        Storage::fake('local');
        $kategori = Kategori::create(['nama' => 'Fiksi']);
        $rak = Rak::create(['kode' => 'R-01', 'nama' => 'Rak Utama']);

        $file = $this->createExcelFile([
            ['isbn' => '978-602-001', 'judul' => 'Buku Pertama', 'sub_judul' => '', 'kategori' => 'Fiksi', 'pengarang' => 'Penulis A', 'penerbit' => 'Penerbit X', 'tahun' => '2024', 'bahasa' => 'Indonesia', 'rak' => 'R-01', 'jumlah_eksemplar' => '5', 'deskripsi' => '', 'status' => 'aktif'],
            ['isbn' => '978-602-002', 'judul' => 'Buku Kedua', 'sub_judul' => 'Sub', 'kategori' => 'Fiksi', 'pengarang' => 'Penulis B', 'penerbit' => 'Penerbit Y', 'tahun' => '2023', 'bahasa' => 'Indonesia', 'rak' => 'R-01', 'jumlah_eksemplar' => '3', 'deskripsi' => 'Desc', 'status' => 'aktif'],
        ]);

        Livewire::actingAs($this->user)
            ->test('buku.buku-import')
            ->set('file', $file)
            ->call('confirmImport');

        $this->assertDatabaseHas('bukus', ['isbn' => '978-602-001', 'judul' => 'Buku Pertama']);
        $this->assertDatabaseHas('bukus', ['isbn' => '978-602-002', 'judul' => 'Buku Kedua']);
    }

    public function test_isbn_duplikat_dilewati(): void
    {
        $kategori = Kategori::create(['nama' => 'Fiksi']);
        $rak = Rak::create(['kode' => 'R-01', 'nama' => 'Rak Utama']);
        Buku::create([
            'kode' => 'BUK-0001', 'isbn' => '978-602-001', 'judul' => 'Sudah Ada',
            'kategori_id' => $kategori->id, 'rak_id' => $rak->id, 'jumlah_eksemplar' => 1, 'status' => 'aktif',
        ]);

        $file = $this->createExcelFile([
            ['isbn' => '978-602-001', 'judul' => 'Duplikat', 'sub_judul' => '', 'kategori' => 'Fiksi', 'pengarang' => '', 'penerbit' => '', 'tahun' => '', 'bahasa' => '', 'rak' => 'R-01', 'jumlah_eksemplar' => '1', 'deskripsi' => '', 'status' => 'aktif'],
            ['isbn' => '978-602-002', 'judul' => 'Baru', 'sub_judul' => '', 'kategori' => 'Fiksi', 'pengarang' => '', 'penerbit' => '', 'tahun' => '', 'bahasa' => '', 'rak' => 'R-01', 'jumlah_eksemplar' => '1', 'deskripsi' => '', 'status' => 'aktif'],
        ]);

        Livewire::actingAs($this->user)
            ->test('buku.buku-import')
            ->set('file', $file)
            ->call('confirmImport');

        $this->assertDatabaseHas('bukus', ['isbn' => '978-602-002', 'judul' => 'Baru']);
        $this->assertEquals(1, Buku::where('isbn', '978-602-001')->count());
    }

    public function test_preview_tidak_menulis_data(): void
    {
        Storage::fake('local');
        $kategori = Kategori::create(['nama' => 'Fiksi']);
        $rak = Rak::create(['kode' => 'R-01', 'nama' => 'Rak Utama']);

        $file = $this->createExcelFile([
            ['isbn' => '978-602-001', 'judul' => 'Buku Preview', 'sub_judul' => '', 'kategori' => 'Fiksi', 'pengarang' => '', 'penerbit' => '', 'tahun' => '', 'bahasa' => '', 'rak' => 'R-01', 'jumlah_eksemplar' => '1', 'deskripsi' => '', 'status' => 'aktif'],
        ]);

        Livewire::actingAs($this->user)
            ->test('buku.buku-import')
            ->set('file', $file)
            ->call('preview')
            ->assertSet('showPreview', true)
            ->assertSet('importResult.imported', 1);

        $this->assertDatabaseMissing('bukus', ['isbn' => '978-602-001']);
    }

    public function test_download_template(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('buku.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function createExcelFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['isbn', 'judul', 'sub_judul', 'kategori', 'pengarang', 'penerbit', 'tahun', 'bahasa', 'rak', 'jumlah_eksemplar', 'deskripsi', 'status'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        foreach ($rows as $rowIndex => $row) {
            $colIndex = 1;
            foreach ($row as $value) {
                $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex + 2, $value);
                $colIndex++;
            }
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'import_buku_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return new NamedUploadedFile($tempFile, 'import_buku.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
