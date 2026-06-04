<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_pemuridan', function (Blueprint $table) {
            $table->id('kelompok_id');
            $table->string('nama_kelompok', 256);
            $table->unsignedBigInteger('kampus_id')->nullable();
            $table->unsignedBigInteger('pemimpin_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('kampus_id')
                ->references('kampus_id')
                ->on('kampus')
                ->nullOnDelete();

            $table->foreign('pemimpin_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('kelompok_id')->nullable()->after('pkk_id');

            $table->foreign('kelompok_id')
                ->references('kelompok_id')
                ->on('kelompok_pemuridan')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kelompok_id']);
            $table->dropColumn('kelompok_id');
        });

        Schema::dropIfExists('kelompok_pemuridan');
    }
};
