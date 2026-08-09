<?php

use App\Imports\BukuImport;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    #[Rule('required|file|mimes:xlsx,xls,csv|max:10240')]
    public $file;

    public $showPreview = false;

    public $importResult = null;

    public function updatedFile(): void
    {
        $this->showPreview = false;
        $this->importResult = null;
    }

    public function preview(): void
    {
        $this->validateOnly('file');

        $import = new BukuImport;
        DB::beginTransaction();
        try {
            $import->import($this->file);
            DB::rollBack();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->importResult = [
            'imported' => $import->imported,
            'skipped' => $import->skipped,
            'errors' => $import->errors,
        ];

        $this->showPreview = true;
    }

    public function confirmImport(): void
    {
        $this->validateOnly('file');

        $import = new BukuImport;
        $import->import($this->file);

        $this->importResult = [
            'imported' => $import->imported,
            'skipped' => $import->skipped,
            'errors' => $import->errors,
        ];

        session()->flash('success', "Import selesai: {$import->imported} berhasil, {$import->skipped} dilewati.");
        $this->showPreview = false;
        $this->file = null;
        $this->importResult = null;
    }

    public function render()
    {
        return view('components.buku.buku-import.buku-import');
    }
};
