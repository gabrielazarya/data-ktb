<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['akk', 'pkk', 'staff', 'admin', 'super_admin'])->default('akk')->change();
        });
    }

    public function down(): void
    {
        if (DB::table('users')->where('role', 'staff')->exists()) {
            throw new RuntimeException('Ubah kategori Staff sebelum membatalkan migration ini.');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['akk', 'pkk', 'admin', 'super_admin'])->default('akk')->change();
        });
    }
};
