<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'must_change_password')) {
            return;
        }

        DB::table('users')
            ->where('role', 'staff')
            ->whereIn(DB::raw('LOWER(TRIM(nama_lengkap))'), [
                'bonan imanuel',
                'didit',
                'happy',
                'marta',
                'ekawaty',
                'akhung',
                'marissa',
            ])
            ->update(['must_change_password' => true]);
    }

    public function down(): void
    {
        // Existing password state is intentionally not guessed on rollback.
    }
};
