<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KampusSeeder extends Seeder
{
    /**
     * Seed 14 kampus default di Surabaya.
     * Data ini dapat diubah/ditambah oleh Super Admin via UI.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $kampusList = [
            ['nama_kampus' => 'Telkom University Surabaya',                            'singkatan' => 'TUS'],
            ['nama_kampus' => 'Universitas Negeri Surabaya',                           'singkatan' => 'UNESA'],
            ['nama_kampus' => 'Universitas Surabaya',                                  'singkatan' => 'UBAYA'],
            ['nama_kampus' => 'Universitas Ciputra Surabaya',                          'singkatan' => 'UC'],
            ['nama_kampus' => 'Universitas Narotama Surabaya',                         'singkatan' => 'UNNAR'],
            ['nama_kampus' => 'Institut Teknologi Adhi Tama Surabaya',                 'singkatan' => 'ITATS'],
            ['nama_kampus' => 'Universitas Airlangga Surabaya',                        'singkatan' => 'UNAIR'],
            ['nama_kampus' => 'Universitas 17 Agustus 1945 Surabaya',                 'singkatan' => 'UNTAG'],
            ['nama_kampus' => 'Universitas Katolik Widya Mandala Surabaya',            'singkatan' => 'UKWMS'],
            ['nama_kampus' => 'Universitas Pembangunan Nasional "Veteran" Jawa Timur', 'singkatan' => 'UPN'],
            ['nama_kampus' => 'Politeknik Kesehatan Kemenkes Surabaya',                'singkatan' => 'POLKESSBY'],
            ['nama_kampus' => 'Politeknik Elektronika Negeri Surabaya',                'singkatan' => 'PENS'],
            ['nama_kampus' => 'Institut Teknologi Sepuluh Nopember Surabaya',          'singkatan' => 'ITS'],
            ['nama_kampus' => 'Universitas Dr. Soetomo Surabaya',                      'singkatan' => 'UNITOMO'],
        ];

        foreach ($kampusList as $kampus) {
            DB::table('kampus')->insertOrIgnore([
                'nama_kampus' => $kampus['nama_kampus'],
                'singkatan'   => $kampus['singkatan'],
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
