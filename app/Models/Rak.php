<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rak extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'keterangan'];

    public function bukus(): HasMany
    {
        return $this->hasMany(Buku::class);
    }
}
