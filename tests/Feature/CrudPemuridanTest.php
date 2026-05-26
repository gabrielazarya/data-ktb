<?php

namespace Tests\Feature;

use App\Models\Kampus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudPemuridanTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_kampus_pkk_and_akk(): void
    {
        $admin = User::query()->create([
            'username' => 'superadmin_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('dashboard.kampus.store'), [
                'nama_kampus' => 'Kampus Test',
                'singkatan' => 'KT',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $kampus = Kampus::query()->where('singkatan', 'KT')->firstOrFail();

        $this->assertDatabaseHas('kampus', [
            'nama_kampus' => 'Kampus Test',
            'singkatan' => 'KT',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('dashboard.pkk.store'), [
                'username' => 'pkk_test',
                'password' => 'password',
                'nama_lengkap' => 'PKK Test',
                'kampus_id' => $kampus->kampus_id,
                'angkatan' => 2022,
                'is_target' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('dashboard.akk.store'), [
                'username' => 'akk_test',
                'password' => 'password',
                'nama_lengkap' => 'AKK Test',
                'kampus_id' => $kampus->kampus_id,
                'pkk_id' => User::query()->where('username', 'pkk_test')->value('user_id'),
                'angkatan' => 2024,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'pkk_test',
            'role' => 'pkk',
            'kampus_id' => $kampus->kampus_id,
            'is_target' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'akk_test',
            'role' => 'akk',
            'kampus_id' => $kampus->kampus_id,
            'pkk_id' => User::query()->where('username', 'pkk_test')->value('user_id'),
        ]);
    }

    public function test_admin_pelihat_cannot_manage_crud(): void
    {
        $admin = User::query()->create([
            'username' => 'admin_pelihat_test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Pelihat Test',
            'role' => 'admin',
            'admin_tipe' => 'pelihat',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('dashboard.kampus.store'), [
                'nama_kampus' => 'Kampus Terlarang',
                'is_active' => '1',
            ])
            ->assertForbidden();
    }
}
