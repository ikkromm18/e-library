<?php

namespace App\Exceptions;

use App\Models\Buku;
use RuntimeException;

class StokHabisException extends RuntimeException
{
    public function __construct(public readonly Buku $buku)
    {
        parent::__construct("Stok buku \"{$buku->judul}\" habis.");
    }
}
