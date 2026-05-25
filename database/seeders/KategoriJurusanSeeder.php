<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriJurusanSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nama_kategori' => 'Ilmu Kesehatan & Kedokteran',                    'keterangan' => 'Kedokteran, Farmasi, Keperawatan, Gizi, Kesehatan Masyarakat, dll'],
            ['nama_kategori' => 'Ilmu Komputer, Informasi, & Logika',             'keterangan' => 'Informatika, Sistem Informasi, Teknik Komputer, Matematika Komputasi, dll'],
            ['nama_kategori' => 'Rekayasa & Teknik (Engineering)',                 'keterangan' => 'Teknik Sipil, Elektro, Mesin, Kimia, Industri, Arsitektur, dll'],
            ['nama_kategori' => 'Ekonomi, Bisnis, & Keuangan',                    'keterangan' => 'Manajemen, Akuntansi, Ekonomi, Bisnis Internasional, Perbankan, dll'],
            ['nama_kategori' => 'Ilmu Sosial, Politik, & Hukum',                  'keterangan' => 'Hukum, Ilmu Politik, Sosiologi, Hubungan Internasional, Administrasi, dll'],
            ['nama_kategori' => 'Matematika & Ilmu Pengetahuan Alam (MIPA)',      'keterangan' => 'Matematika, Fisika, Kimia, Biologi, Statistika, dll'],
            ['nama_kategori' => 'Ilmu Humaniora & Budaya',                         'keterangan' => 'Sastra, Filsafat, Sejarah, Linguistik, Psikologi, dll'],
            ['nama_kategori' => 'Ilmu Pendidikan & Keguruan',                     'keterangan' => 'Pendidikan Guru, PGSD, Bimbingan Konseling, Teknologi Pendidikan, dll'],
            ['nama_kategori' => 'Seni, Desain, & Media Kreatif',                  'keterangan' => 'Desain Grafis, Seni Rupa, DKV, Komunikasi, Broadcast, Film, dll'],
            ['nama_kategori' => 'Pertanian, Kelautan, & Biosfer',                 'keterangan' => 'Pertanian, Perikanan, Kehutanan, Peternakan, Bioteknologi, dll'],
        ];

        foreach ($categories as &$cat) {
            $cat['created_at'] = now();
            $cat['updated_at'] = now();
        }

        DB::table('kategori_jurusan')->insert($categories);
    }
}
