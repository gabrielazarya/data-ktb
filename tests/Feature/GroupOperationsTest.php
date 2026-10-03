<?php

namespace Tests\Feature;

use App\Models\Kampus;
use App\Models\KelompokPemuridan;
use App\Models\Regio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_transfer_a_member_and_keep_the_origin_campus(): void
    {
        [$admin, $campus, $otherCampus] = $this->baseData();
        $leader = $this->user('leader_transfer', 'Pemimpin Lama', $campus);
        $newLeader = $this->user('leader_transfer_new', 'Pemimpin Baru', $otherCampus);
        $member = $this->user('member_transfer', 'Anggota Pindahan', $campus);
        $source = $this->group('Kelompok Sumber', $campus, $leader);
        $target = $this->group('Kelompok Tujuan', $otherCampus, $newLeader);
        $member->forceFill(['kelompok_id' => $source->kelompok_id, 'pkk_id' => $leader->user_id])->save();

        $this->actingAs($admin)->post(route('dashboard.pohon.anggota.transfer', $member), [
            'kelompok_id' => $target->kelompok_id,
            'reason' => 'Penataan kelompok lintas kampus',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertSame($target->kelompok_id, $member->kelompok_id);
        $this->assertSame($newLeader->user_id, $member->pkk_id);
        $this->assertSame($campus->kampus_id, $member->kampus_id);
        $this->assertDatabaseHas('mentorships', [
            'mentor_id' => $newLeader->user_id,
            'mentee_id' => $member->user_id,
            'status' => 'active',
        ]);
    }

    public function test_editor_can_reassign_leader_without_removing_the_group(): void
    {
        [$admin, $campus] = $this->baseData();
        $oldLeader = $this->user('leader_reassign_old', 'Pemimpin Lama', $campus);
        $newLeader = $this->user('leader_reassign_new', 'Pemimpin Baru', $campus);
        $group = $this->group('Kelompok Reassign', $campus, $oldLeader);

        $this->actingAs($admin)->post(route('dashboard.pohon.kelompok.leader', $group), [
            'pemimpin_id' => $newLeader->user_id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($newLeader->user_id, $group->refresh()->pemimpin_id);
        $this->assertSame('pkk', $newLeader->refresh()->role);
        $this->assertDatabaseHas('group_leader_histories', [
            'kelompok_id' => $group->kelompok_id,
            'leader_id' => $newLeader->user_id,
            'is_current' => true,
        ]);
    }

    public function test_group_and_member_archive_routes_preserve_history(): void
    {
        [$admin, $campus] = $this->baseData();
        $leader = $this->user('leader_archive', 'Pemimpin Arsip', $campus);
        $member = $this->user('member_archive', 'Anggota Arsip', $campus);
        $group = $this->group('Kelompok Arsip', $campus, $leader);
        $member->forceFill(['kelompok_id' => $group->kelompok_id, 'pkk_id' => $leader->user_id])->save();

        $this->actingAs($admin)->post(route('dashboard.pohon.kelompok.archive', $group), [
            'reason' => 'Periode pelayanan selesai',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertFalse((bool) $group->refresh()->is_active);
        $this->assertDatabaseHas('kelompok_pemuridan', ['kelompok_id' => $group->kelompok_id]);

        $this->actingAs($admin)->post(route('dashboard.pohon.anggota.archive', $member), [
            'reason' => 'Tidak lagi aktif',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertFalse((bool) $member->refresh()->is_active);
        $this->assertDatabaseHas('users', ['user_id' => $member->user_id]);
    }

    public function test_group_can_receive_a_primary_campus_assignment(): void
    {
        [$admin, $campus, $otherCampus] = $this->baseData();
        $leader = $this->user('leader_campus', 'Pemimpin Kampus', $campus);
        $group = $this->group('Kelompok Multi Kampus', $campus, $leader);

        $this->actingAs($admin)->post(route('dashboard.pohon.kelompok.campus.assign', $group), [
            'kampus_id' => $otherCampus->kampus_id,
            'is_primary' => true,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($otherCampus->kampus_id, $group->refresh()->kampus_id);
    }

    private function baseData(): array
    {
        $regio = Regio::query()->firstOrFail();
        $admin = User::query()->create([
            'username' => 'operations_admin_'.uniqid(),
            'password' => 'password',
            'nama_lengkap' => 'Admin Operasional',
            'role' => 'admin',
            'admin_tipe' => 'editor',
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]);
        $campus = Kampus::query()->create(['nama_kampus' => 'Kampus Operasional A', 'regio_id' => $regio->regio_id, 'is_active' => true]);
        $otherCampus = Kampus::query()->create(['nama_kampus' => 'Kampus Operasional B', 'regio_id' => $regio->regio_id, 'is_active' => true]);

        return [$admin, $campus, $otherCampus];
    }

    private function user(string $username, string $name, Kampus $campus): User
    {
        return User::query()->create([
            'username' => $username,
            'password' => 'password',
            'nama_lengkap' => $name,
            'role' => 'pkk',
            'kampus_id' => $campus->kampus_id,
            'regio_id' => $campus->regio_id,
            'is_active' => true,
        ]);
    }

    private function group(string $name, Kampus $campus, User $leader): KelompokPemuridan
    {
        return KelompokPemuridan::query()->create([
            'nama_kelompok' => $name,
            'kampus_id' => $campus->kampus_id,
            'regio_id' => $campus->regio_id,
            'pemimpin_id' => $leader->user_id,
            'is_active' => true,
        ]);
    }
}
