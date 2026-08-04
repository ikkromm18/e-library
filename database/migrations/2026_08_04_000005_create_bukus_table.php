<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukus', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('isbn')->nullable()->unique();
            $table->string('judul');
            $table->string('sub_judul')->nullable();
            $table->foreignId('kategori_id')->constrained('kategoris');
            $table->string('pengarang')->nullable();
            $table->string('penerbit')->nullable();
            $table->smallInteger('tahun')->unsigned()->nullable();
            $table->string('bahasa')->nullable();
            $table->foreignId('rak_id')->constrained('raks');
            $table->unsignedInteger('jumlah_eksemplar')->default(1);
            $table->text('deskripsi')->nullable();
            $table->string('cover')->nullable();
            $table->enum('status', ['aktif', 'tidak'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukus');
    }
};
