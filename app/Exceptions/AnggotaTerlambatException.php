<?php

namespace App\Exceptions;

use RuntimeException;

class AnggotaTerlambatException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Anggota masih memiliki buku terlambat yang belum dikembalikan.');
    }
}
