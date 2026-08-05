<?php

namespace App\Exceptions;

use RuntimeException;

class AnggotaNonaktifException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Anggota tidak aktif, tidak bisa meminjam buku.');
    }
}
