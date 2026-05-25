<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ganti kolom enum 'kategori_jurusan' yang panjang
     * menjadi FK integer ke tabel kategori_jurusan yang lebih efisien.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom enum lama
            $table->dropColumn('kategori_jurusan');

            // Tambah FK ke tabel kategori_jurusan
            $table->unsignedBigInteger('kategori_jurusan_id')
                  ->nullable()
                  ->after('jurusan');

            $table->foreign('kategori_jurusan_id')
                  ->references('kategori_jurusan_id')
                  ->on('kategori_jurusan')
                  ->nullOnDelete();
        });
    }

    /**
     * Rollback: kembalikan ke enum (untuk keperluan testing).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kategori_jurusan_id']);
            $table->dropColumn('kategori_jurusan_id');

            $table->enum('kategori_jurusan', [
                'Ilmu Kesehatan & Kedokteran',
                'Ilmu Komputer, Informasi, & Logika',
                'Rekayasa & Teknik (Engineering)',
                'Ekonomi, Bisnis, & Keuangan',
                'Ilmu Sosial, Politik, & Hukum',
                'Matematika & Ilmu Pengetahuan Alam (MIPA)',
                'Ilmu Humaniora & Budaya',
                'Ilmu Pendidikan & Keguruan',
                'Seni, Desain, & Media Kreatif',
                'Pertanian, Kelautan, & Biosfer',
            ])->nullable()->after('jurusan');
        });
    }
};
