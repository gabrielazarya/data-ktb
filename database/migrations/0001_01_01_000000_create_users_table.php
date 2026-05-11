<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel users untuk Sistem KTB
     * Mendukung 4 role: akk, pkk, admin, super_admin
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary key
            $table->id('user_id'); // INT(11) AUTO_INCREMENT

            // Kredensial login (berbasis username, bukan email)
            $table->string('username', 256)->unique();
            $table->string('password', 256);

            // Data personal
            $table->string('nama_lengkap', 256);
            $table->date('tanggal_lahir')->nullable();

            // Asal kampus — FK ke tabel kampus (ditambahkan setelah tabel kampus dibuat)
            // Sementara nullable, akan diisi setelah kampus dipilih
            $table->unsignedBigInteger('kampus_id')->nullable();

            // Angkatan (tahun, contoh: 2022, 2023)
            $table->smallInteger('angkatan')->unsigned()->nullable();

            // Role dalam sistem KTB
            $table->enum('role', ['akk', 'pkk', 'admin', 'super_admin'])->default('akk');

            // Data tambahan
            $table->string('foto_profil', 256)->nullable();

            // Label klasifikasi target/non-target (khusus PKK, diset oleh Admin)
            $table->boolean('is_target')->default(false);

            // Untuk Admin: klasifikasi akses (pelihat = read only, editor = full edit)
            $table->enum('admin_tipe', ['pelihat', 'editor'])->nullable();

            // Status akun
            $table->boolean('is_active')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('username')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
