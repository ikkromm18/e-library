<?php

namespace App\Exceptions;

use RuntimeException;

class MelebihiLimitException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Jumlah buku melebihi batas maksimal peminjaman.');
    }
}
