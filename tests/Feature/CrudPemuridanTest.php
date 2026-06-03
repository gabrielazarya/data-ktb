<?php

namespace Tests\Feature;

use App\Models\Kampus;
use App\Models\KategoriJurusan;
use App\Models\KelompokPemuridan;
use App\Models\Regio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrudPemuridanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_editor_can_create_kampus(): void
    {
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();
        $admin = User::query()->create([
            'username' => 'admin_kampus_test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Kampus Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $surabaya->regio_id,
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

    public function test_admin_cannot_access_regio_or_pengguna_pages(): void
    {
        $admin = User::query()->create([
            'username' => 'admin_restricted_pages_test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Restricted Pages Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'is_active' => true,
        ]);
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Komposisi KTB')
            ->assertDontSee('Kelola Data Pemuridan')
            ->assertDontSee('Informasi Akun')
            ->assertDontSee('Kontrol Akses Pusat')
            ->assertDontSee('>Regio</a>', false)
            ->assertDontSee('>Pengguna</a>', false);

        $this->actingAs($admin)
            ->get(route('dashboard.regio'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('dashboard.pengguna'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('dashboard.regio.store'), [
                'nama_regio' => 'Regio Admin Terlarang',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('dashboard.pengguna.store'), [
                'nama_lengkap' => 'Admin Terlarang',
                'regio_id' => $surabaya->regio_id,
                'admin_tipe' => 'pelihat',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('dashboard.pengguna.switch-access', $admin))
            ->assertForbidden();
    }

    public function test_super_admin_cannot_access_operational_pages(): void
    {
        $superAdmin = User::query()->create([
            'username' => 'superadmin_operational_restricted_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Operational Restricted Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Regio Terbaru')
            ->assertDontSee('Kontrol Akses Pusat')
            ->assertDontSee('Informasi Akun')
            ->assertDontSee('Kelola Data Pemuridan')
            ->assertSee('>Pengguna</a>', false)
            ->assertSee('>Regio</a>', false)
            ->assertDontSee('>Kampus</a>', false)
            ->assertDontSee('>Anggota KTB</a>', false)
            ->assertDontSee('>Pohon</a>', false);

        $content = $response->getContent();
        $penggunaPosition = strpos($content, '>Pengguna</a>');
        $regioPosition = strpos($content, '>Regio</a>');

        $this->assertNotFalse($penggunaPosition);
        $this->assertNotFalse($regioPosition);
        $this->assertLessThan($regioPosition, $penggunaPosition);

        $this->actingAs($superAdmin)
            ->get(route('dashboard.kampus'))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->get(route('dashboard.anggota-ktb'))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->get(route('dashboard.pohon'))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->post(route('dashboard.kampus.store'), [
                'nama_kampus' => 'Kampus Superadmin Terlarang',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->post(route('dashboard.pohon.anggota.store'), [
                'nama_lengkap' => 'Anggota Superadmin Terlarang',
                'is_active' => '1',
            ])
            ->assertForbidden();
    }

    public function test_admin_is_scoped_to_own_regio_for_data_and_mutations(): void
    {
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();
        $malang = Regio::query()->create([
            'nama_regio' => 'Malang Scope Test',
            'keterangan' => 'Regio pembanding',
            'is_active' => true,
        ]);
        $admin = User::query()->create([
            'username' => 'admin_scope_test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Scope Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $kampusSurabaya = Kampus::query()->create([
            'nama_kampus' => 'Kampus Surabaya Scope',
            'singkatan' => 'KSS',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $kampusSurabayaOther = Kampus::query()->create([
            'nama_kampus' => 'Kampus Surabaya Scope Lain',
            'singkatan' => 'KSSL',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $kampusMalang = Kampus::query()->create([
            'nama_kampus' => 'Kampus Malang Scope',
            'singkatan' => 'KMS',
            'regio_id' => $malang->regio_id,
            'is_active' => true,
        ]);
        $memberSurabaya = User::query()->create([
            'username' => 'akk_scope_surabaya',
            'password' => 'password',
            'nama_lengkap' => 'AKK Scope Surabaya',
            'role' => 'akk',
            'kampus_id' => $kampusSurabaya->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        User::query()->create([
            'username' => 'akk_scope_surabaya_lain',
            'password' => 'password',
            'nama_lengkap' => 'AKK Scope Surabaya Lain',
            'role' => 'akk',
            'kampus_id' => $kampusSurabayaOther->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $memberMalang = User::query()->create([
            'username' => 'akk_scope_malang',
            'password' => 'password',
            'nama_lengkap' => 'AKK Scope Malang',
            'role' => 'akk',
            'kampus_id' => $kampusMalang->kampus_id,
            'regio_id' => $malang->regio_id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.kampus'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kampus Surabaya Scope')
            ->assertDontSee('Semua Kampus')
            ->assertSee('KSS')
            ->assertDontSee('Kampus Malang Scope')
            ->assertDontSee('Pilih regio');

        $this->actingAs($admin)
            ->get(route('dashboard.kampus.show', $kampusSurabaya))
            ->assertOk()
            ->assertSee('Detail Kampus')
            ->assertSee('Kampus Surabaya Scope')
            ->assertSee('Pohon Pemuridan')
            ->assertSee('Anggota KTB')
            ->assertSee('AKK Scope Surabaya')
            ->assertSee('Grafik pohon pemuridan')
            ->assertDontSee('Direktori Kampus')
            ->assertDontSee('AKK Scope Surabaya Lain')
            ->assertDontSee('AKK Scope Malang');

        $this->actingAs($admin)
            ->get(route('dashboard.kampus.tab', ['kampus' => $kampusSurabaya, 'tab' => 'pohon']))
            ->assertOk()
            ->assertSee('Grafik pohon pemuridan')
            ->assertSee('AKK Scope Surabaya')
            ->assertDontSee('AKK Scope Surabaya Lain')
            ->assertDontSee('AKK Scope Malang');

        $this->actingAs($admin)
            ->get(route('dashboard.kampus.tab', ['kampus' => $kampusSurabaya, 'tab' => 'anggota']))
            ->assertOk()
            ->assertSee('Anggota KTB KSS')
            ->assertSee('AKK Scope Surabaya')
            ->assertDontSee('AKK Scope Surabaya Lain')
            ->assertDontSee('AKK Scope Malang');

        $this->actingAs($admin)
            ->get(route('dashboard.kampus.show', $kampusMalang))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('dashboard.kampus.tab', ['kampus' => $kampusMalang, 'tab' => 'anggota']))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('dashboard.anggota-ktb'))
            ->assertOk()
            ->assertSee('AKK Scope Surabaya')
            ->assertDontSee('AKK Scope Malang');

        $this->actingAs($admin)
            ->get(route('dashboard.pohon'))
            ->assertOk()
            ->assertSee('Kampus Surabaya Scope')
            ->assertDontSee('Kampus Malang Scope')
            ->assertSee('Editor - Surabaya')
            ->assertDontSee('Malang Scope Test');

        $this->actingAs($admin)
            ->post(route('dashboard.kampus.store'), [
                'nama_kampus' => 'Kampus Baru Scope',
                'singkatan' => 'KBS',
                'regio_id' => $malang->regio_id,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kampus', [
            'nama_kampus' => 'Kampus Baru Scope',
            'regio_id' => $surabaya->regio_id,
        ]);

        $this->actingAs($admin)
            ->put(route('dashboard.kampus.update', $kampusMalang), [
                'nama_kampus' => 'Kampus Malang Diubah Terlarang',
                'singkatan' => 'KMT',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.anggota.store'), [
                'nama_lengkap' => 'AKK Malang Terlarang',
                'kampus_id' => $kampusMalang->kampus_id,
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.anggota.store'), [
                'nama_lengkap' => 'AKK Baru Surabaya Scope',
                'kampus_id' => $kampusSurabaya->kampus_id,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'nama_lengkap' => 'AKK Baru Surabaya Scope',
            'kampus_id' => $kampusSurabaya->kampus_id,
            'regio_id' => $surabaya->regio_id,
        ]);

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.kelompok.store'), [
                'pemimpin_id' => $memberMalang->user_id,
                'nama_kelompok' => 'Kelompok Malang Terlarang',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.kelompok.store'), [
                'pemimpin_id' => $memberSurabaya->user_id,
                'kampus_id' => $kampusMalang->kampus_id,
                'nama_kelompok' => 'Kelompok Target Malang Terlarang',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.kelompok.store'), [
                'pemimpin_id' => $memberSurabaya->user_id,
                'nama_kelompok' => 'Kelompok Surabaya Scope',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kelompok_pemuridan', [
            'nama_kelompok' => 'Kelompok Surabaya Scope',
            'pemimpin_id' => $memberSurabaya->user_id,
            'regio_id' => $surabaya->regio_id,
        ]);
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
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('regios', [
            'regio_id' => $regio->regio_id,
            'nama_regio' => 'Regio Test Update',
            'keterangan' => 'Wilayah update',
            'is_active' => true,
        ]);

        User::query()->create([
            'username' => 'admin_regio_count_test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Regio Count Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.regio'))
            ->assertOk()
            ->assertSee('Regio Test Update')
            ->assertSee('Wilayah update')
            ->assertSee('<th>Admin</th>', false)
            ->assertDontSee('<th>Aktif</th>', false)
            ->assertSee('Tambah Regio');
    }

    public function test_pengguna_and_anggota_ktb_pages_are_separated_by_role(): void
    {
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();
        $superAdmin = User::query()->create([
            'username' => 'superadmin_direktori_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Direktori Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $admin = User::query()->create([
            'username' => 'admin_direktori_test',
            'password' => 'password',
            'nama_lengkap' => 'Operator Direktori Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $kampus = Kampus::query()->create([
            'nama_kampus' => 'Kampus Direktori Test',
            'singkatan' => 'KDT',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

        $pkk = User::query()->create([
            'username' => 'pkk_direktori_test',
            'password' => 'password',
            'nama_lengkap' => 'PKK Direktori Test',
            'role' => 'pkk',
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

        $akk = User::query()->create([
            'username' => 'akk_direktori_test',
            'password' => 'password',
            'nama_lengkap' => 'AKK Direktori Test',
            'role' => 'akk',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard.pengguna'))
            ->assertOk()
            ->assertSee('Operator Direktori Test')
            ->assertDontSee('superadmin_direktori_test')
            ->assertSee('PKK Direktori Test')
            ->assertSee('AKK Direktori Test')
            ->assertSee('KDT')
            ->assertDontSee('Dibuat')
            ->assertDontSee('<th>Kampus</th>', false)
            ->assertSee('<option value="admin" selected>Admin</option>', false)
            ->assertSee('<option value="">Semua</option>', false);

        $this->actingAs($superAdmin)
            ->get(route('dashboard.anggota-ktb'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('dashboard.anggota-ktb'))
            ->assertOk()
            ->assertSee('PKK Direktori Test')
            ->assertSee('AKK Direktori Test')
            ->assertDontSee('<th>Kampus</th>', false)
            ->assertSee('<option value="" selected>Semua</option>', false)
            ->assertSee('<option value="pkk">PKK</option>', false)
            ->assertSee('<option value="akk">AKK</option>', false)
            ->assertSee('data-column-filter="role"', false)
            ->assertSee('data-modal-open="modal-member-group-create-'.$pkk->user_id.'"', false)
            ->assertSee('data-modal-open="modal-member-group-create-'.$akk->user_id.'"', false)
            ->assertSee('Kampus Kelompok');

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.kelompok.store'), [
                '_modal_id' => 'modal-member-group-create-'.$akk->user_id,
                'pemimpin_id' => $akk->user_id,
                'kampus_id' => $kampus->kampus_id,
                'nama_kelompok' => 'Kelompok Dari Anggota Direktori',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kelompok_pemuridan', [
            'nama_kelompok' => 'Kelompok Dari Anggota Direktori',
            'pemimpin_id' => $akk->user_id,
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $akk->user_id,
            'role' => 'pkk',
        ]);
    }

    public function test_super_admin_can_manage_admin_users_from_pengguna_page(): void
    {
        $superAdmin = User::query()->create([
            'username' => 'superadmin_admin_user_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Admin User Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();
        $malang = Regio::query()->create([
            'nama_regio' => 'Malang Test',
            'keterangan' => 'Regio update admin',
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard.pengguna'))
            ->assertOk()
            ->assertSee('Tambah Admin');

        $this->actingAs($superAdmin)
            ->post(route('dashboard.pengguna.store'), [
                'nama_lengkap' => 'Admin Baru Test',
                'regio_id' => $surabaya->regio_id,
                'admin_tipe' => 'pelihat',
            ])
            ->assertRedirect();

        $admin = User::query()->where('nama_lengkap', 'Admin Baru Test')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertSame('admin'.$admin->user_id, $admin->username);
        $this->assertSame('pelihat', $admin->admin_tipe);
        $this->assertSame($surabaya->regio_id, $admin->regio_id);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('admin', $admin->password));

        $this->actingAs($superAdmin)
            ->put(route('dashboard.pengguna.update', $admin), [
                'nama_lengkap' => 'Admin Update Test',
                'regio_id' => $malang->regio_id,
                'admin_tipe' => 'editor',
            ])
            ->assertRedirect();

        $admin->refresh();
        $this->assertSame('Admin Update Test', $admin->nama_lengkap);
        $this->assertSame('admin'.$admin->user_id, $admin->username);
        $this->assertSame('editor', $admin->admin_tipe);
        $this->assertSame($malang->regio_id, $admin->regio_id);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('admin', $admin->password));

        $this->actingAs($superAdmin)
            ->delete(route('dashboard.pengguna.destroy', $admin))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'user_id' => $admin->user_id,
        ]);
    }

    public function test_admin_editor_can_update_and_delete_tree_members_and_groups(): void
    {
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();
        $admin = User::query()->create([
            'username' => 'admin_tree_edit_test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Tree Edit Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $kampus = Kampus::query()->create([
            'nama_kampus' => 'Kampus Tree Edit Test',
            'singkatan' => 'KTET',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $leader = User::query()->create([
            'username' => 'leader_tree_edit_test',
            'password' => 'password',
            'nama_lengkap' => 'Leader Tree Edit Test',
            'role' => 'pkk',
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $group = KelompokPemuridan::query()->create([
            'nama_kelompok' => 'Kelompok Tree Edit Test',
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'pemimpin_id' => $leader->user_id,
            'is_active' => true,
        ]);
        $member = User::query()->create([
            'username' => 'member_tree_edit_test',
            'password' => 'password',
            'nama_lengkap' => 'Member Tree Edit Test',
            'role' => 'akk',
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'pkk_id' => $leader->user_id,
            'kelompok_id' => $group->kelompok_id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.pohon'))
            ->assertOk()
            ->assertSee('data-tree-v2-action-do="edit_member"', false)
            ->assertSee('data-tree-v2-action-do="delete_member"', false)
            ->assertSee('data-tree-v2-action-do="edit_group"', false)
            ->assertSee('data-tree-v2-action-do="delete_group"', false)
            ->assertSee('data-person-id="'.$member->user_id.'"', false)
            ->assertSee('data-group-id="'.$group->kelompok_id.'"', false);

        $this->actingAs($admin)
            ->put(route('dashboard.pohon.anggota.update', $member), [
                '_modal_id' => 'tree-member-edit-modal',
                '_tree_person_id' => $member->user_id,
                'nama_lengkap' => 'Member Tree Updated Test',
                'kampus_id' => $kampus->kampus_id,
                'angkatan' => 2024,
                'is_active' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'user_id' => $member->user_id,
            'nama_lengkap' => 'Member Tree Updated Test',
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'angkatan' => 2024,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->put(route('dashboard.pohon.kelompok.update', $group), [
                '_modal_id' => 'tree-group-edit-modal',
                '_tree_group_id' => $group->kelompok_id,
                'nama_kelompok' => 'Kelompok Tree Updated Test',
                'kampus_id' => $kampus->kampus_id,
                'is_active' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kelompok_pemuridan', [
            'kelompok_id' => $group->kelompok_id,
            'nama_kelompok' => 'Kelompok Tree Updated Test',
            'kampus_id' => $kampus->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('dashboard.pohon.kelompok.destroy', $group))
            ->assertRedirect();

        $this->assertDatabaseMissing('kelompok_pemuridan', [
            'kelompok_id' => $group->kelompok_id,
        ]);
        $this->assertDatabaseHas('users', [
            'user_id' => $leader->user_id,
            'role' => 'akk',
        ]);
        $this->assertDatabaseHas('users', [
            'user_id' => $member->user_id,
            'pkk_id' => null,
            'kelompok_id' => null,
        ]);

        $this->actingAs($admin)
            ->delete(route('dashboard.pohon.anggota.destroy', $member))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'user_id' => $member->user_id,
        ]);
    }

    public function test_super_admin_can_switch_to_admin_access_and_return(): void
    {
        $superAdmin = User::query()->create([
            'username' => 'superadmin_switch_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Switch Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();
        $admin = User::query()->create([
            'username' => 'admin_switch_test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Switch Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $pkk = User::query()->create([
            'username' => 'pkk_switch_test',
            'password' => 'password',
            'nama_lengkap' => 'PKK Switch Test',
            'role' => 'pkk',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $akk = User::query()->create([
            'username' => 'akk_switch_test',
            'password' => 'password',
            'nama_lengkap' => 'AKK Switch Test',
            'role' => 'akk',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard.pengguna'))
            ->assertOk()
            ->assertSee('Pindah akses');

        $this->actingAs($superAdmin)
            ->post(route('dashboard.pengguna.switch-access', $admin))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('impersonator_super_admin_id', $superAdmin->user_id);

        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Sedang memakai akses admin')
            ->assertSee('Kembali')
            ->assertDontSee('Logout');

        $this->post(route('dashboard.access.return'))
            ->assertRedirect(route('superadmin.dashboard'))
            ->assertSessionMissing('impersonator_super_admin_id');

        $this->assertAuthenticatedAs($superAdmin);

        $this->actingAs($superAdmin)
            ->post(route('dashboard.pengguna.switch-access', $pkk))
            ->assertRedirect(route('pkk.dashboard'))
            ->assertSessionHas('impersonator_super_admin_id', $superAdmin->user_id);

        $this->assertAuthenticatedAs($pkk);

        $this->post(route('dashboard.access.return'))
            ->assertRedirect(route('superadmin.dashboard'))
            ->assertSessionMissing('impersonator_super_admin_id');

        $this->assertAuthenticatedAs($superAdmin);

        $this->actingAs($superAdmin)
            ->post(route('dashboard.pengguna.switch-access', $akk))
            ->assertRedirect(route('akk.dashboard'))
            ->assertSessionHas('impersonator_super_admin_id', $superAdmin->user_id);

        $this->assertAuthenticatedAs($akk);
    }

    public function test_authenticated_user_can_view_update_profile_and_change_password(): void
    {
        $superAdmin = User::query()->create([
            'username' => 'superadmin_profile_test',
            'password' => 'password',
            'nama_lengkap' => 'Super Admin Profile Test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $kategori = KategoriJurusan::query()->create([
            'nama_kategori' => 'Kategori Profil Test',
            'keterangan' => 'Kategori untuk test profil',
        ]);
        $user = User::query()->create([
            'username' => 'profile_test_user',
            'password' => 'password',
            'nama_lengkap' => 'Profile Test User',
            'role' => 'akk',
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard.profile'))
            ->assertOk()
            ->assertSee('Detail Akun')
            ->assertDontSee('<span class="detail-label">Kampus</span>', false)
            ->assertDontSee('<span class="detail-label">Tanggal Lahir</span>', false)
            ->assertDontSee('<span class="detail-label">Jurusan</span>', false)
            ->assertDontSee('<span class="detail-label">Kategori Jurusan</span>', false)
            ->assertDontSee('<span class="detail-label">Angkatan</span>', false)
            ->assertDontSee('<span class="detail-label">PKK</span>', false)
            ->assertDontSee('<span class="detail-label">Kelompok KTB</span>', false)
            ->assertDontSee('id="profile-birth-date"', false)
            ->assertDontSee('id="profile-year"', false)
            ->assertDontSee('id="profile-major"', false)
            ->assertDontSee('id="profile-major-category"', false);

        $this->actingAs($user)
            ->get(route('dashboard.profile'))
            ->assertOk()
            ->assertSee('Detail Akun')
            ->assertSee('Data Profil')
            ->assertSee('Ubah Password');

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'nama_lengkap' => 'Profile Test Updated',
                'tanggal_lahir' => '2001-02-03',
                'jurusan' => 'Informatika',
                'kategori_jurusan_id' => $kategori->kategori_jurusan_id,
                'angkatan' => 2020,
                'foto_profil' => 'images/profile-test.png',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Profile Test Updated', $user->nama_lengkap);
        $this->assertSame('Informatika', $user->jurusan);
        $this->assertSame($kategori->kategori_jurusan_id, $user->kategori_jurusan_id);
        $this->assertSame(2020, $user->angkatan);
        $this->assertSame('images/profile-test.png', $user->foto_profil);

        $this->actingAs($user)
            ->put(route('dashboard.profile.password'), [
                'current_password' => 'salah',
                'password' => 'passwordbaru',
                'password_confirmation' => 'passwordbaru',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put(route('dashboard.profile.password'), [
                'current_password' => 'password',
                'password' => 'passwordbaru',
                'password_confirmation' => 'passwordbaru',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('passwordbaru', $user->refresh()->password));
    }

    public function test_pohon_displays_empty_campus_and_admin_editor_can_manage_tree_flow(): void
    {
        $surabaya = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();
        $admin = User::query()->create([
            'username' => 'admin_tree_test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Tree Test',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

        $kampus = Kampus::query()->create([
            'nama_kampus' => 'Kampus Kosong',
            'singkatan' => 'KK',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);
        $kampusLintas = Kampus::query()->create([
            'nama_kampus' => 'Kampus Lintas Tree Test',
            'singkatan' => 'KLT',
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

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

        $userCountBeforeCrossCampusGroup = User::query()->count();

        $this->actingAs($admin)
            ->post(route('dashboard.pohon.kelompok.store'), [
                'pemimpin_id' => $pkk->user_id,
                'kampus_id' => $kampusLintas->kampus_id,
                'nama_kelompok' => 'Kelompok Lintas Kampus Tree Test',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertSame($userCountBeforeCrossCampusGroup, User::query()->count());
        $this->assertDatabaseHas('kelompok_pemuridan', [
            'nama_kelompok' => 'Kelompok Lintas Kampus Tree Test',
            'pemimpin_id' => $pkk->user_id,
            'kampus_id' => $kampusLintas->kampus_id,
            'regio_id' => $surabaya->regio_id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.kampus.tab', ['kampus' => $kampusLintas, 'tab' => 'pohon']))
            ->assertOk()
            ->assertSee('Anggota Kampus Tree Test')
            ->assertSee('Kelompok Lintas Kampus Tree Test');

        $this->actingAs($admin)
            ->get(route('dashboard.kampus.tab', ['kampus' => $kampus, 'tab' => 'pohon']))
            ->assertOk()
            ->assertSee('Kelompok Anggota Kampus Tree Test')
            ->assertDontSee('Kelompok Lintas Kampus Tree Test');

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
