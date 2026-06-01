<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $surabayaId = $this->ensureSurabayaRegio();

        if (! Schema::hasColumn('kampus', 'regio_id')) {
            Schema::table('kampus', function (Blueprint $table) {
                $table->unsignedBigInteger('regio_id')->nullable()->after('kampus_id');

                $table->foreign('regio_id')
                    ->references('regio_id')
                    ->on('regios')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('kelompok_pemuridan', 'regio_id')) {
            Schema::table('kelompok_pemuridan', function (Blueprint $table) {
                $table->unsignedBigInteger('regio_id')->nullable()->after('kampus_id');

                $table->foreign('regio_id')
                    ->references('regio_id')
                    ->on('regios')
                    ->nullOnDelete();
            });
        }

        $now = now();

        DB::table('kampus')->update([
            'regio_id' => $surabayaId,
            'updated_at' => $now,
        ]);

        DB::table('kelompok_pemuridan')->update([
            'regio_id' => $surabayaId,
            'updated_at' => $now,
        ]);

        DB::table('users')
            ->where('role', 'super_admin')
            ->update([
                'regio_id' => null,
                'updated_at' => $now,
            ]);

        DB::table('users')
            ->where('role', '!=', 'super_admin')
            ->update([
                'regio_id' => $surabayaId,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('kelompok_pemuridan', 'regio_id')) {
            Schema::table('kelompok_pemuridan', function (Blueprint $table) {
                $table->dropForeign(['regio_id']);
                $table->dropColumn('regio_id');
            });
        }

        if (Schema::hasColumn('kampus', 'regio_id')) {
            Schema::table('kampus', function (Blueprint $table) {
                $table->dropForeign(['regio_id']);
                $table->dropColumn('regio_id');
            });
        }
    }

    private function ensureSurabayaRegio(): int
    {
        $now = now();
        $existingId = DB::table('regios')
            ->where('nama_regio', 'Surabaya')
            ->value('regio_id');

        if ($existingId) {
            DB::table('regios')
                ->where('regio_id', $existingId)
                ->update([
                    'keterangan' => 'Wilayah pelayanan PMK Kota Surabaya',
                    'is_active' => true,
                    'updated_at' => $now,
                ]);

            return (int) $existingId;
        }

        return (int) DB::table('regios')->insertGetId([
            'nama_regio' => 'Surabaya',
            'keterangan' => 'Wilayah pelayanan PMK Kota Surabaya',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
