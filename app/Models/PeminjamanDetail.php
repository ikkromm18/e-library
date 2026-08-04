<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanDetail extends Model
{
    protected $fillable = [
        'peminjaman_id', 'buku_id', 'tanggal_kembali', 'keterlambatan_hari',
    ];

    protected $casts = [
        'tanggal_kembali' => 'date',
        'keterlambatan_hari' => 'integer',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class);
    }
}
