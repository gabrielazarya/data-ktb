<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel regio untuk membedakan wilayah pelayanan PMK.
     * Contoh: Surabaya, Malang.
     */
    public function up(): void
    {
        Schema::create('regios', function (Blueprint $table) {
            $table->id('regio_id');
            $table->string('nama_regio', 100)->unique(); // Contoh: "Surabaya", "Malang"
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regios');
    }
};
