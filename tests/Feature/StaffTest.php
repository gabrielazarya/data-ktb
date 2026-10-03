<?php

namespace Tests\Feature;

use App\Models\Kampus;
use App\Models\KelompokPemuridan;
use App\Models\Regio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_lead_two_campuses_without_a_personal_campus(): void
    {
        $regio = Regio::query()->where('nama_regio', 'Surabaya')->firstOrFail();
        $admin = User::query()->create([
            'username' => 'staff_test_admin',
            'password' => 'password',
            'nama_lengkap' => 'Admin Staff',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]);
        $campuses = collect(['Kampus Staff A', 'Kampus Staff B'])->map(fn (string $name) => Kampus::query()->create([
            'nama_kampus' => $name,
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]));

        $this->actingAs($admin)->post(route('dashboard.pohon.anggota.store'), [
            'nama_lengkap' => 'Staff Lintas Kampus',
            'is_staff' => '1',
            'kampus_id' => $campuses->first()->kampus_id,
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $staff = User::query()->where('nama_lengkap', 'Staff Lintas Kampus')->firstOrFail();
        $this->assertTrue($staff->isStaff());
        $this->assertNull($staff->kampus_id);
        $this->assertTrue($staff->must_change_password);

        foreach ($campuses as $campus) {
            $this->actingAs($admin)->post(route('dashboard.pohon.kelompok.store'), [
                'pemimpin_id' => $staff->user_id,
                'kampus_id' => $campus->kampus_id,
                'nama_kelompok' => 'Kelompok '.$campus->nama_kampus,
                'is_active' => '1',
            ])->assertRedirect()->assertSessionHasNoErrors();
        }

        $this->assertTrue($staff->refresh()->isStaff());
        $this->assertNull($staff->kampus_id);
        $groups = $staff->kelompokDipimpin()->get();
        $this->assertCount(2, $groups);

        $this->actingAs($staff)->get(route('dashboard'))->assertRedirect(route('pkk.dashboard'));
        $this->actingAs($staff)->get(route('pkk.dashboard'))->assertOk();
        $this->actingAs($staff)->get(route('pkk.kelompok'))->assertOk()
            ->assertSee('Kampus Staff A')->assertSee('Kampus Staff B');
        $this->actingAs($staff)->get(route('dashboard.profile'))->assertOk();
        foreach ($groups as $group) {
            $this->actingAs($staff)->post(route('pkk.kelompok.laporan.store', $group), [
                'tanggal_pertemuan' => '2026-10-03',
                'bahan' => 'Bahan Staff',
            ])->assertRedirect()->assertSessionHasNoErrors();
        }

        $foreignGroup = KelompokPemuridan::query()->create([
            'nama_kelompok' => 'Kelompok Orang Lain',
            'pemimpin_id' => $admin->user_id,
            'kampus_id' => $campuses->first()->kampus_id,
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]);
        $this->actingAs($staff)->post(route('pkk.kelompok.laporan.store', $foreignGroup), [
            'tanggal_pertemuan' => '2026-10-03',
            'bahan' => 'Tidak diizinkan',
        ])->assertForbidden();
        $this->actingAs($staff)->post(route('dashboard.pohon.kelompok.store'), [])->assertForbidden();

        $this->actingAs($admin)->get(route('dashboard.pohon'))->assertOk()->assertSee('Staff Lintas Kampus');
        $this->actingAs($admin)->put(route('dashboard.pohon.anggota.update', $staff), [
            'nama_lengkap' => 'Staff Lintas Kampus',
            'is_staff' => '1',
            'kampus_id' => $campuses->first()->kampus_id,
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($staff->refresh()->kampus_id);
        $this->actingAs($admin)->delete(route('dashboard.pohon.kelompok.destroy', $groups->first()))->assertRedirect();
        $this->assertTrue($staff->refresh()->isStaff());
    }
}
