<?php

namespace Database\Seeders;

use App\Models\Kampus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyPemuridanSeeder extends Seeder
{
    /**
     * Seed data dummy PKK dan AKK untuk kebutuhan demo dashboard.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make('dummy123');

        $campusData = [
            'TUS' => [
                'pkk' => [
                    ['username' => 'pkk_tus_01', 'nama_lengkap' => 'Maria Claudia', 'angkatan' => 2021, 'is_target' => true],
                    ['username' => 'pkk_tus_02', 'nama_lengkap' => 'Daniel Wicaksono', 'angkatan' => 2020, 'is_target' => false],
                ],
                'akk' => [
                    ['username' => 'akk_tus_01', 'nama_lengkap' => 'Ester Natalia', 'angkatan' => 2023],
                    ['username' => 'akk_tus_02', 'nama_lengkap' => 'Yosua Pratama', 'angkatan' => 2023],
                    ['username' => 'akk_tus_03', 'nama_lengkap' => 'Grace Olivia', 'angkatan' => 2024],
                    ['username' => 'akk_tus_04', 'nama_lengkap' => 'Samuel Kevin', 'angkatan' => 2024],
                ],
            ],
            'UNESA' => [
                'pkk' => [
                    ['username' => 'pkk_unesa_01', 'nama_lengkap' => 'Ruth Maharani', 'angkatan' => 2021, 'is_target' => true],
                    ['username' => 'pkk_unesa_02', 'nama_lengkap' => 'Andreas Putra', 'angkatan' => 2020, 'is_target' => false],
                ],
                'akk' => [
                    ['username' => 'akk_unesa_01', 'nama_lengkap' => 'Debora Sihombing', 'angkatan' => 2023],
                    ['username' => 'akk_unesa_02', 'nama_lengkap' => 'Mikael Jonathan', 'angkatan' => 2023],
                    ['username' => 'akk_unesa_03', 'nama_lengkap' => 'Naomi Christy', 'angkatan' => 2024],
                ],
            ],
            'UBAYA' => [
                'pkk' => [
                    ['username' => 'pkk_ubaya_01', 'nama_lengkap' => 'Filipus Christian', 'angkatan' => 2020, 'is_target' => true],
                    ['username' => 'pkk_ubaya_02', 'nama_lengkap' => 'Theresia Monica', 'angkatan' => 2021, 'is_target' => false],
                ],
                'akk' => [
                    ['username' => 'akk_ubaya_01', 'nama_lengkap' => 'Immanuel Ezra', 'angkatan' => 2023],
                    ['username' => 'akk_ubaya_02', 'nama_lengkap' => 'Kezia Abigail', 'angkatan' => 2024],
                    ['username' => 'akk_ubaya_03', 'nama_lengkap' => 'Jason Timothy', 'angkatan' => 2024],
                    ['username' => 'akk_ubaya_04', 'nama_lengkap' => 'Michelle Hana', 'angkatan' => 2023],
                ],
            ],
            'UNAIR' => [
                'pkk' => [
                    ['username' => 'pkk_unair_01', 'nama_lengkap' => 'Paulus Hartono', 'angkatan' => 2020, 'is_target' => true],
                    ['username' => 'pkk_unair_02', 'nama_lengkap' => 'Lydia Angeline', 'angkatan' => 2021, 'is_target' => false],
                ],
                'akk' => [
                    ['username' => 'akk_unair_01', 'nama_lengkap' => 'Gabriel Nathan', 'angkatan' => 2023],
                    ['username' => 'akk_unair_02', 'nama_lengkap' => 'Phoebe Valencia', 'angkatan' => 2024],
                    ['username' => 'akk_unair_03', 'nama_lengkap' => 'Benedict Allen', 'angkatan' => 2024],
                ],
            ],
            'ITS' => [
                'pkk' => [
                    ['username' => 'pkk_its_01', 'nama_lengkap' => 'Yohanes Adrian', 'angkatan' => 2020, 'is_target' => true],
                    ['username' => 'pkk_its_02', 'nama_lengkap' => 'Priscilla Irene', 'angkatan' => 2021, 'is_target' => false],
                ],
                'akk' => [
                    ['username' => 'akk_its_01', 'nama_lengkap' => 'Caleb Wirawan', 'angkatan' => 2023],
                    ['username' => 'akk_its_02', 'nama_lengkap' => 'Tabita Michelle', 'angkatan' => 2023],
                    ['username' => 'akk_its_03', 'nama_lengkap' => 'Nataniel Surya', 'angkatan' => 2024],
                    ['username' => 'akk_its_04', 'nama_lengkap' => 'Joanna Felicia', 'angkatan' => 2024],
                ],
            ],
        ];

        foreach ($campusData as $singkatan => $roles) {
            $kampus = Kampus::query()->where('singkatan', $singkatan)->first();

            if (! $kampus) {
                continue;
            }

            foreach ($roles['pkk'] as $row) {
                $this->upsertUser($row, 'pkk', $kampus->kampus_id, $password, $now);
            }

            foreach ($roles['akk'] as $row) {
                $this->upsertUser($row, 'akk', $kampus->kampus_id, $password, $now);
            }
        }
    }

    private function upsertUser(array $row, string $role, int $kampusId, string $password, Carbon $now): void
    {
        DB::table('users')->updateOrInsert(
            ['username' => $row['username']],
            [
                'password' => $password,
                'nama_lengkap' => $row['nama_lengkap'],
                'tanggal_lahir' => null,
                'kampus_id' => $kampusId,
                'angkatan' => $row['angkatan'],
                'role' => $role,
                'foto_profil' => null,
                'is_target' => (bool) ($row['is_target'] ?? false),
                'admin_tipe' => null,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
