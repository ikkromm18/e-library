<?php

namespace App\Http\Controllers;

use App\Exports\AnggotaExport;
use App\Exports\BukuExport;
use App\Exports\TransaksiExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function export(string $tipe, string $format, Request $request): Response
    {
        $dari = $request->string('dari') ?: null;
        $sampai = $request->string('sampai') ?: null;

        $export = match ($tipe) {
            'buku' => new BukuExport(
                $request->integer('kategori_id') ?: null,
                $request->integer('rak_id') ?: null,
            ),
            'anggota' => new AnggotaExport($request->string('status') ?: null, $dari, $sampai),
            'transaksi' => new TransaksiExport($request->string('jenis') ?: 'peminjaman', $dari, $sampai),
            default => abort(404),
        };

        if ($format === 'excel') {
            return Excel::download($export, "$tipe.xlsx");
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView("reports.pdf.$tipe", [
                'rows' => $export->collection(),
                'headings' => $export->headings(),
            ]);

            return $pdf->download("$tipe.pdf");
        }

        abort(404);
    }
}
