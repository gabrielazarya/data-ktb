<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Urutan penting: Kampus harus di-seed dulu sebelum Users
     * karena users.kampus_id adalah FK ke kampus.kampus_id
     */
    public function run(): void
    {
        $this->call([
            KampusSeeder::class,     // 1. Seed 14 kampus default
            SuperAdminSeeder::class, // 2. Seed akun Super Admin
        ]);
    }
}
