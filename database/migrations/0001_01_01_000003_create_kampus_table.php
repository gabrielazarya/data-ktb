<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel kampus — dikelola dinamis oleh Super Admin
     * Menggantikan ENUM statis di tabel users
     */
    public function up(): void
    {
        Schema::create('kampus', function (Blueprint $table) {
            $table->id('kampus_id');
            $table->string('nama_kampus', 256)->unique();
            $table->string('singkatan', 50)->nullable(); // contoh: TUS, UNESA, UBAYA
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tambahkan Foreign Key dari users.kampus_id → kampus.kampus_id
        // setelah kedua tabel sudah dibuat
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('kampus_id')
                  ->references('kampus_id')
                  ->on('kampus')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kampus_id']);
        });

        Schema::dropIfExists('kampus');
    }
};
