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
            KampusSeeder::class,          // 1. Master kampus
            RegioSeeder::class,           // 2. Master regio (Surabaya, Malang)
            KategoriJurusanSeeder::class, // 3. Master 10 kategori jurusan
            SuperAdminSeeder::class,      // 4. Akun Super Admin
        ]);
    }
}
