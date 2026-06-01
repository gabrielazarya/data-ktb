<?php

namespace Tests\Feature;

use App\Models\Kampus;
use App\Models\KelompokPemuridan;
use App\Models\Regio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();

        $this->assertDatabaseHas('kampus', [
            'nama_kampus' => 'Kampus Test',
            'singkatan' => 'KT',
            'regio_id' => $surabaya->regio_id,
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

    public function test_super_admin_can_create_and_update_regio(): void
    {
        $admin = User::query()->create([
            'username' => 'superadmin_regio_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Regio Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('dashboard.regio.store'), [
                'nama_regio' => 'Regio Test',
                'keterangan' => 'Wilayah test',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('regios', [
            'nama_regio' => 'Regio Test',
            'keterangan' => 'Wilayah test',
            'is_active' => true,
        ]);

        $regio = Regio::query()->where('nama_regio', 'Regio Test')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('dashboard.regio.update', $regio), [
                'nama_regio' => 'Regio Test Update',
                'keterangan' => 'Wilayah update',
                'is_active' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('regios', [
            'regio_id' => $regio->regio_id,
            'nama_regio' => 'Regio Test Update',
            'keterangan' => 'Wilayah update',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.regio'))
            ->assertOk()
            ->assertSee('Regio Test Update')
            ->assertSee('Wilayah update')
            ->assertSee('Tambah Regio');
    }

    public function test_pengguna_and_anggota_ktb_pages_are_separated_by_role(): void
    {
        $superAdmin = User::query()->create([
            'username' => 'superadmin_direktori_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Direktori Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        User::query()->create([
            'username' => 'admin_direktori_test',
            'password' => 'password',
            'nama_lengkap' => 'Operator Direktori Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'is_active' => true,
        ]);

        User::query()->create([
            'username' => 'pkk_direktori_test',
            'password' => 'password',
            'nama_lengkap' => 'PKK Direktori Test',
            'role' => 'pkk',
            'is_active' => true,
        ]);

        User::query()->create([
            'username' => 'akk_direktori_test',
            'password' => 'password',
            'nama_lengkap' => 'AKK Direktori Test',
            'role' => 'akk',
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard.pengguna'))
            ->assertOk()
            ->assertSee('Super Admin Direktori Test')
            ->assertSee('Operator Direktori Test')
            ->assertDontSee('PKK Direktori Test')
            ->assertDontSee('AKK Direktori Test');

        $this->actingAs($superAdmin)
            ->get(route('dashboard.anggota-ktb'))
            ->assertOk()
            ->assertSee('PKK Direktori Test')
            ->assertSee('AKK Direktori Test')
            ->assertDontSee('Operator Direktori Test');
    }

    public function test_pohon_displays_empty_campus_and_super_admin_can_manage_tree_flow(): void
    {
        $admin = User::query()->create([
            'username' => 'superadmin_tree_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Tree Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $kampus = Kampus::query()->create([
            'nama_kampus' => 'Kampus Kosong',
            'singkatan' => 'KK',
            'regio_id' => Regio::query()->where('nama_regio', 'Surabaya')->value('regio_id'),
            'is_active' => true,
        ]);
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard.pohon'))
            ->assertOk()
            ->assertSee('Kampus Kosong')
            ->assertSee('Belum ada anggota')
            ->assertSee('Tambah Anggota');

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.anggota.store'), [
                'nama_lengkap' => 'Anggota Kampus Tree Test',
                'kampus_id' => $kampus->kampus_id,
                'angkatan' => 2025,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'nama_lengkap' => 'Anggota Kampus Tree Test',
            'role' => 'akk',
            'pkk_id' => null,
            'kelompok_id' => null,
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'angkatan' => 2025,
            'is_active' => true,
        ]);

        $campusMember = User::query()->where('nama_lengkap', 'Anggota Kampus Tree Test')->firstOrFail();
        $this->assertSame('user'.$campusMember->user_id, $campusMember->username);
        $this->assertTrue(Hash::check('user', $campusMember->password));

        $response = $this->actingAs($admin)
            ->get(route('dashboard.pohon'))
            ->assertOk();

        $this->assertMatchesRegularExpression(
            '/data-node-name="Anggota Kampus Tree Test"[\s\S]*?<span class="badge\s*">AKK<\/span>/',
            $response->getContent()
        );

        $pkk = $campusMember;

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.kelompok.store'), [
                'pemimpin_id' => $pkk->user_id,
                'nama_kelompok' => 'Kelompok Anggota Kampus Tree Test',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kelompok_pemuridan', [
            'nama_kelompok' => 'Kelompok Anggota Kampus Tree Test',
            'pemimpin_id' => $pkk->user_id,
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $pkk->user_id,
            'role' => 'pkk',
        ]);

        $group = KelompokPemuridan::query()->where('nama_kelompok', 'Kelompok Anggota Kampus Tree Test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard.pohon'))
            ->assertOk()
            ->assertSee('Anggota Kampus Tree Test')
            ->assertSee('Kelompok Anggota Kampus Tree Test');

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.anggota.store'), [
                'kelompok_id' => $group->kelompok_id,
                'nama_lengkap' => 'Anggota Tree Test',
                'angkatan' => 2026,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'nama_lengkap' => 'Anggota Tree Test',
            'role' => 'akk',
            'pkk_id' => $pkk->user_id,
            'kelompok_id' => $group->kelompok_id,
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'angkatan' => 2026,
            'is_active' => true,
        ]);

        $member = User::query()->where('nama_lengkap', 'Anggota Tree Test')->firstOrFail();
        $this->assertSame('user'.$member->user_id, $member->username);
        $this->assertTrue(Hash::check('user', $member->password));

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.kelompok.store'), [
                'pemimpin_id' => $member->user_id,
                'nama_kelompok' => 'Kelompok Anak Tree Test',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'user_id' => $member->user_id,
            'role' => 'pkk',
            'pkk_id' => $pkk->user_id,
            'kelompok_id' => $group->kelompok_id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('dashboard.pohon'))
            ->assertOk();

        $this->assertMatchesRegularExpression(
            '/data-node-name="Kelompok Anggota Kampus Tree Test"[\s\S]*?<ul class="tree-v2-children tree-v2-level-members">[\s\S]*?data-node-name="Anggota Tree Test"[\s\S]*?data-node-name="Kelompok Anak Tree Test"/',
            $response->getContent()
        );
    }
}
