<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom regio_id, jurusan, dan kategori_jurusan ke tabel users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Wilayah/regio pengguna (FK ke tabel regios)
            // Ditempatkan setelah kampus_id agar logis secara urutan data
            $table->unsignedBigInteger('regio_id')->nullable()->after('kampus_id');

            // Jurusan kuliah (teks bebas, contoh: "Teknik Informatika")
            $table->string('jurusan', 255)->nullable()->after('regio_id');

            // Kategori jurusan (enum terstandar)
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

            // Foreign key constraint ke tabel regios
            $table->foreign('regio_id')
                  ->references('regio_id')
                  ->on('regios')
                  ->nullOnDelete();
        });
    }

    /**
     * Rollback: hapus kolom yang ditambahkan.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['regio_id']);
            $table->dropColumn(['regio_id', 'jurusan', 'kategori_jurusan']);
        });
    }
};
