<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Urutan penting: regio dibuat dulu supaya kampus dan user bisa terhubung.
     */
    public function run(): void
    {
        $this->call([
            RegioSeeder::class,           // 1. Master regio (Surabaya, Malang)
            KampusSeeder::class,          // 2. Master kampus
            KategoriJurusanSeeder::class, // 3. Master 10 kategori jurusan
            SuperAdminSeeder::class,      // 4. Akun Super Admin
        ]);
    }
}
