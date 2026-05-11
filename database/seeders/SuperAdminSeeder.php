<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed akun Super Admin default.
     * PENTING: Ganti password setelah pertama kali login!
     */
    public function run(): void
    {
        DB::table('users')->insertOrIgnore([
            'username'      => 'superadmin',
            'password'      => Hash::make('superadmin123'), // Ganti setelah login pertama!
            'nama_lengkap'  => 'Super Administrator',
            'tanggal_lahir' => null,
            'kampus_id'     => null,
            'angkatan'      => null,
            'role'          => 'super_admin',
            'foto_profil'   => null,
            'is_target'     => false,
            'admin_tipe'    => null,
            'is_active'     => true,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);
    }
}
