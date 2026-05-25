<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel master kategori jurusan.
     * Dibuat terpisah agar mudah diubah/ditambah tanpa ALTER TABLE users.
     */
    public function up(): void
    {
        Schema::create('kategori_jurusan', function (Blueprint $table) {
            $table->id('kategori_jurusan_id');
            $table->string('nama_kategori', 100)->unique();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_jurusan');
    }
};
