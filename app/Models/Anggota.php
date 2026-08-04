<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggota';

    protected $fillable = [
        'nis', 'nama', 'jenis_kelamin', 'kelas', 'alamat', 'no_hp', 'tanggal_masuk', 'status',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    public function peminjamen(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }
}
