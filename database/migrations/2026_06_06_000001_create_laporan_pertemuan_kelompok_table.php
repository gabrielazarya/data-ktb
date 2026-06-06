<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_pertemuan_kelompok', function (Blueprint $table) {
            $table->id('laporan_id');
            $table->unsignedBigInteger('kelompok_id');
            $table->unsignedBigInteger('pkk_id')->nullable();
            $table->date('tanggal_pertemuan');
            $table->unsignedSmallInteger('pertemuan_ke')->nullable();
            $table->text('bahan');
            $table->text('ringkasan')->nullable();
            $table->json('anggota_hadir')->nullable();
            $table->unsignedSmallInteger('jumlah_hadir')->default(0);
            $table->text('catatan')->nullable();
            $table->text('rencana_lanjutan')->nullable();
            $table->text('kendala')->nullable();
            $table->timestamps();

            $table->foreign('kelompok_id')
                ->references('kelompok_id')
                ->on('kelompok_pemuridan')
                ->cascadeOnDelete();

            $table->foreign('pkk_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['kelompok_id', 'tanggal_pertemuan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_pertemuan_kelompok');
    }
};
