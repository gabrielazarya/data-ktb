<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('regios')->insertOrIgnore([
            [
                'nama_regio'  => 'Surabaya',
                'keterangan'  => 'Wilayah pelayanan PMK Kota Surabaya',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama_regio'  => 'Malang',
                'keterangan'  => 'Wilayah pelayanan PMK Kota Malang',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
