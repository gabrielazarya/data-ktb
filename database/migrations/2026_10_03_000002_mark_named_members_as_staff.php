<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The named members are staff and therefore must not remain attached to a
     * campus or carry student-only academic metadata.
     */
    public function up(): void
    {
        $payload = [
            'role' => 'staff',
            'kampus_id' => null,
            'angkatan' => null,
            'jurusan' => null,
            'kategori_jurusan_id' => null,
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('users', 'must_change_password')) {
            $payload['must_change_password'] = true;
        }

        DB::table('users')
            ->whereIn(DB::raw('LOWER(TRIM(nama_lengkap))'), [
                'bonan imanuel',
                'didit',
                'happy',
                'marta',
                'ekawaty',
                'akhung',
                'marissa',
            ])
            ->update($payload);
    }

    public function down(): void
    {
        // The previous role/campus values are not known reliably enough to
        // restore automatically. Staff status can be adjusted from the UI.
    }
};
