<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudPemuridanTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_kampus(): void
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

        $this->assertDatabaseHas('kampus', [
            'nama_kampus' => 'Kampus Test',
            'singkatan' => 'KT',
            'is_active' => true,
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
