<?php

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use Livewire\Component;

new class extends Component
{
    public $totalEksemplar = 0;

    public $totalJudul = 0;

    public $totalAnggota = 0;

    public $sedangDipinjam = 0;

    public $terlambat = 0;

    public $kembaliHariIni = 0;

    public $grafik = [];

    public $maksGrafik = 1;

    public function render()
    {
        $this->totalEksemplar = (int) Buku::sum('jumlah_eksemplar');
        $this->totalJudul = Buku::count();
        $this->totalAnggota = Anggota::count();
        $this->sedangDipinjam = PeminjamanDetail::whereNull('tanggal_kembali')
            ->whereHas('peminjaman', fn ($q) => $q->where('status', 'dipinjam'))
            ->count();
        $this->terlambat = PeminjamanDetail::whereNull('tanggal_kembali')
            ->whereHas('peminjaman', fn ($q) => $q
                ->where('status', 'dipinjam')
                ->whereDate('tanggal_jatuh_tempo', '<', today()))
            ->count();
        $this->kembaliHariIni = PeminjamanDetail::whereDate('tanggal_kembali', today())->count();

        $perHari = Peminjaman::where('tanggal', '>=', today()->subDays(6))
            ->select('tanggal')
            ->selectRaw('count(*) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->tanggal->format('Y-m-d') => (int) $item->total]);

        $this->grafik = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = today()->subDays($i);
            $this->grafik[$tanggal->format('Y-m-d')] = (int) ($perHari[$tanggal->format('Y-m-d')] ?? 0);
        }
        $this->maksGrafik = max(1, max(array_values($this->grafik)));

        return view('components.dashboard.dashboard');
    }
};
