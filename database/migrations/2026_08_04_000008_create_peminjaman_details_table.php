<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjamen')->cascadeOnDelete();
            $table->foreignId('buku_id')->constrained('bukus');
            $table->date('tanggal_kembali')->nullable();
            $table->unsignedSmallInteger('keterlambatan_hari')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_details');
    }
};
