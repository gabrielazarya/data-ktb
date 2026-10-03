<?php

namespace Tests\Feature;

use App\Models\FollowUpTask;
use App\Models\GroupMembership;
use App\Models\Kampus;
use App\Models\KelompokPemuridan;
use App\Models\MeetingAttendance;
use App\Models\Regio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_persists_statused_attendance_and_creates_follow_up_for_absence_streak(): void
    {
        $regio = Regio::query()->firstOrFail();
        $campus = Kampus::query()->create([
            'nama_kampus' => 'Kampus Pertemuan Test',
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]);
        $pkk = User::query()->create([
            'username' => 'pkk_meeting_test',
            'password' => 'password',
            'nama_lengkap' => 'PKK Meeting Test',
            'role' => 'pkk',
            'kampus_id' => $campus->kampus_id,
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]);
        $absent = User::query()->create([
            'username' => 'member_absent_test',
            'password' => 'password',
            'nama_lengkap' => 'Member Absent Test',
            'role' => 'akk',
            'kampus_id' => $campus->kampus_id,
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]);
        $present = User::query()->create([
            'username' => 'member_present_test',
            'password' => 'password',
            'nama_lengkap' => 'Member Present Test',
            'role' => 'akk',
            'kampus_id' => $campus->kampus_id,
            'regio_id' => $regio->regio_id,
            'is_active' => true,
        ]);
        $group = KelompokPemuridan::query()->create([
            'nama_kelompok' => 'Kelompok Meeting Test',
            'kampus_id' => $campus->kampus_id,
            'regio_id' => $regio->regio_id,
            'pemimpin_id' => $pkk->user_id,
            'is_active' => true,
        ]);

        foreach ([$absent, $present] as $member) {
            $member->forceFill([
                'kelompok_id' => $group->kelompok_id,
                'pkk_id' => $pkk->user_id,
            ])->save();
            GroupMembership::query()->create([
                'user_id' => $member->user_id,
                'kelompok_id' => $group->kelompok_id,
                'role' => 'member',
                'status' => 'active',
                'started_at' => now(),
            ]);
        }

        foreach (['2026-09-26', '2026-10-03'] as $date) {
            $this->actingAs($pkk)->post(route('pkk.kelompok.laporan.store', $group), [
                'tanggal_pertemuan' => $date,
                'bahan' => 'Materi pengujian',
                'attendance_statuses' => [
                    $absent->user_id => 'alpa',
                    $present->user_id => 'hadir',
                ],
            ])->assertRedirect()->assertSessionHasNoErrors();
        }

        $latest = $group->laporanPertemuan()->latest('laporan_id')->firstOrFail();
        $this->assertSame('alpa', MeetingAttendance::query()
            ->where('meeting_id', $latest->laporan_id)
            ->where('user_id', $absent->user_id)
            ->value('status'));
        $this->assertSame('hadir', MeetingAttendance::query()
            ->where('meeting_id', $latest->laporan_id)
            ->where('user_id', $present->user_id)
            ->value('status'));
        $this->assertDatabaseHas('follow_up_tasks', [
            'kelompok_id' => $group->kelompok_id,
            'user_id' => $absent->user_id,
            'reason' => 'absen_berturut',
            'status' => FollowUpTask::STATUS_OPEN,
        ]);
    }
}
