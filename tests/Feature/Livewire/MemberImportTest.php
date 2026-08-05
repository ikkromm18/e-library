<?php

namespace Tests\Feature\Livewire;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Storage;
use Tests\TestCase;

class MemberImportTest extends TestCase
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

        $file = $this->createExcelFile([
            ['nis' => '20240001', 'nama' => 'Ahmad Fauzi', 'jenis_kelamin' => 'L', 'kelas' => '7A', 'alamat' => 'Jl. Merdeka', 'no_hp' => '081234567890', 'tanggal_masuk' => '2024-07-01', 'status' => 'aktif'],
            ['nis' => '20240002', 'nama' => 'Siti Rahmawati', 'jenis_kelamin' => 'P', 'kelas' => '8B', 'alamat' => '', 'no_hp' => '', 'tanggal_masuk' => '2024-07-01', 'status' => 'aktif'],
        ]);

        Livewire::actingAs($this->user)
            ->test('anggota.member-import')
            ->set('file', $file)
            ->call('confirmImport');

        $this->assertDatabaseHas('anggota', ['nis' => '20240001']);
        $this->assertDatabaseHas('anggota', ['nis' => '20240002']);
    }

    public function test_nis_duplikat_dilewati(): void    {
        Anggota::create([
            'nis' => '20240001', 'nama' => 'Sudah Ada', 'jenis_kelamin' => 'L',
            'tanggal_masuk' => '2024-07-01', 'status' => 'aktif',
        ]);

        $file = $this->createExcelFile([
            ['nis' => '20240001', 'nama' => 'Duplikat', 'jenis_kelamin' => 'L', 'kelas' => '7A', 'alamat' => '', 'no_hp' => '', 'tanggal_masuk' => '2024-07-01', 'status' => 'aktif'],
            ['nis' => '20240002', 'nama' => 'Baru', 'jenis_kelamin' => 'P', 'kelas' => '8B', 'alamat' => '', 'no_hp' => '', 'tanggal_masuk' => '2024-07-01', 'status' => 'aktif'],
        ]);

        Livewire::actingAs($this->user)
            ->test('anggota.member-import')
            ->set('file', $file)
            ->call('confirmImport');

        $this->assertDatabaseHas('anggota', ['nis' => '20240002', 'nama' => 'Baru']);
        $this->assertEquals(1, Anggota::where('nis', '20240001')->count());
    }

    public function test_preview_tidak_menulis_data(): void
    {
        Storage::fake('local');

        $file = $this->createExcelFile([
            ['nis' => '20240001', 'nama' => 'Ahmad Fauzi', 'jenis_kelamin' => 'L', 'kelas' => '7A', 'alamat' => '', 'no_hp' => '', 'tanggal_masuk' => '2024-07-01', 'status' => 'aktif'],
        ]);

        Livewire::actingAs($this->user)
            ->test('anggota.member-import')
            ->set('file', $file)
            ->call('preview')
            ->assertSet('showPreview', true)
            ->assertSet('importResult.imported', 1);

        $this->assertDatabaseMissing('anggota', ['nis' => '20240001']);
    }

    public function test_download_template(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('anggota.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function createExcelFile(array $rows): UploadedFile
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['nis', 'nama', 'jenis_kelamin', 'kelas', 'alamat', 'no_hp', 'tanggal_masuk', 'status'];
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

        $tempFile = tempnam(sys_get_temp_dir(), 'import_');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);

        return new NamedUploadedFile($tempFile, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}

class NamedUploadedFile extends UploadedFile
{
    public string $name;

    public function __construct(string $path, string $originalName, ?string $mimeType = null, ?int $error = null, bool $test = false)
    {
        $this->name = $originalName;

        parent::__construct($path, $originalName, $mimeType, $error, $test);
    }
}
