<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bukus', function (Blueprint $table) {
            $table->index('status');
            $table->index('kategori_id');
            $table->index('rak_id');
        });

        Schema::table('peminjaman_details', function (Blueprint $table) {
            $table->index('tanggal_kembali');
            $table->index('buku_id');
        });

        Schema::table('peminjamen', function (Blueprint $table) {
            $table->index('status');
            $table->index('anggota_id');
        });
    }

    public function down(): void
    {
        Schema::table('bukus', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['kategori_id']);
            $table->dropIndex(['rak_id']);
        });

        Schema::table('peminjaman_details', function (Blueprint $table) {
            $table->dropIndex(['tanggal_kembali']);
            $table->dropIndex(['buku_id']);
        });

        Schema::table('peminjamen', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['anggota_id']);
        });
    }
};
