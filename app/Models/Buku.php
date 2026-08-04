<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buku extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode', 'isbn', 'judul', 'sub_judul', 'kategori_id', 'pengarang', 'penerbit',
        'tahun', 'bahasa', 'rak_id', 'jumlah_eksemplar', 'deskripsi', 'cover', 'status',
    ];

    protected $casts = [
        'jumlah_eksemplar' => 'integer',
        'tahun' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function rak(): BelongsTo
    {
        return $this->belongsTo(Rak::class);
    }

    public function detailsAktif(): HasMany
    {
        return $this->hasMany(PeminjamanDetail::class)
            ->whereHas('peminjaman', fn ($q) => $q->where('status', 'dipinjam'))
            ->whereNull('tanggal_kembali');
    }

    public function stokTersedia(): int
    {
        return $this->jumlah_eksemplar - $this->detailsAktif()->count();
    }

    public static function buatKode(): string
    {
        $n = static::count() + 1;
        while (static::where('kode', $kode = 'BUK-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT))->exists()) {
            $n++;
        }

        return $kode;
    }
}
