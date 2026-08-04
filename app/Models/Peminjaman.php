<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamen';

    protected $fillable = [
        'no_transaksi', 'tanggal', 'tanggal_jatuh_tempo', 'petugas_id', 'anggota_id', 'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_jatuh_tempo' => 'date',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PeminjamanDetail::class);
    }

    public function hitungKeterlambatan(?\Carbon\Carbon $dikembalikan = null): int
    {
        $end = $dikembalikan ?? now()->startOfDay();

        if ($end->gt($this->tanggal_jatuh_tempo)) {
            return (int) $this->tanggal_jatuh_tempo->diffInDays($end);
        }

        return 0;
    }

    public function sudahSelesai(): bool
    {
        return $this->details()->whereNull('tanggal_kembali')->doesntExist();
    }

    public static function generateNoTransaksi(): string
    {
        $prefix = 'PJM-'.now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        while (static::where('no_transaksi', $no = $prefix.'-'.$count)->exists()) {
            $count++;
        }

        return $no;
    }
}
