<?php

namespace App\Http\Controllers;

use App\Models\FollowUpTask;
use App\Models\KelompokPemuridan;
use App\Models\LaporanPertemuanKelompok;
use App\Models\MeetingAttendance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PkkReportController extends Controller
{
    public function store(Request $request, KelompokPemuridan $kelompok): RedirectResponse
    {
        $pkk = $this->authorizeOwnedGroup($kelompok);
        abort_unless($kelompok->is_active, 422, 'Laporan tidak dapat dibuat untuk kelompok yang sudah diarsipkan.');
        $payload = $this->validateReportPayload($request, $kelompok);

        DB::transaction(function () use ($payload, $kelompok, $pkk): void {
            $report = LaporanPertemuanKelompok::query()->create([
                ...$payload,
                'kelompok_id' => $kelompok->kelompok_id,
                'pkk_id' => $pkk->user_id,
            ]);

            $this->syncAttendance($report, $payload['attendance_statuses'] ?? [], $this->groupMemberIds($kelompok));
            $this->createAutomaticFollowUpTasks($report, $pkk);
        });

        return back()->with('success', 'Laporan pertemuan '.$kelompok->nama_kelompok.' berhasil disimpan.');
    }

    public function update(Request $request, LaporanPertemuanKelompok $laporan): RedirectResponse
    {
        $laporan->loadMissing('kelompok');
        $this->authorizeOwnedReport($laporan);
        abort_unless($laporan->kelompok?->is_active, 422, 'Laporan kelompok yang diarsipkan tidak dapat diubah.');

        $payload = $this->validateReportPayload($request, $laporan->kelompok);
        DB::transaction(function () use ($laporan, $payload): void {
            $laporan->update($payload);
            $this->syncAttendance($laporan, $payload['attendance_statuses'] ?? [], $this->groupMemberIds($laporan->kelompok));
            $this->createAutomaticFollowUpTasks($laporan, Auth::user());
        });

        return back()->with('success', 'Laporan pertemuan berhasil diperbarui.');
    }

    public function destroy(LaporanPertemuanKelompok $laporan): RedirectResponse
    {
        $laporan->loadMissing('kelompok');
        $this->authorizeOwnedReport($laporan);

        $laporan->delete();

        return back()->with('success', 'Laporan pertemuan berhasil dihapus.');
    }

    private function validateReportPayload(Request $request, KelompokPemuridan $kelompok): array
    {
        $validated = $request->validate([
            'tanggal_pertemuan' => ['required', 'date'],
            'pertemuan_ke' => ['nullable', 'integer', 'between:1,999'],
            'bahan' => ['required', 'string', 'max:2000'],
            'ringkasan' => ['nullable', 'string', 'max:5000'],
            'anggota_hadir' => ['nullable', 'array'],
            'anggota_hadir.*' => ['integer', Rule::exists('users', 'user_id')],
            'attendance' => ['nullable', 'array'],
            'attendance.*' => ['nullable'],
            'attendance_statuses' => ['nullable', 'array'],
            'attendance_statuses.*' => ['nullable', Rule::in(['hadir', 'izin', 'sakit', 'alpa'])],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'rencana_lanjutan' => ['nullable', 'string', 'max:5000'],
            'kendala' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'tanggal_pertemuan' => 'tanggal pertemuan',
            'pertemuan_ke' => 'pertemuan ke',
            'bahan' => 'bahan yang dibahas',
            'ringkasan' => 'ringkasan pembahasan',
            'anggota_hadir' => 'anggota yang hadir',
            'catatan' => 'catatan',
            'rencana_lanjutan' => 'rencana tindak lanjut',
            'kendala' => 'kendala',
        ]);

        $duplicateQuery = LaporanPertemuanKelompok::query()
            ->where('kelompok_id', $kelompok->kelompok_id)
            ->whereDate('tanggal_pertemuan', $validated['tanggal_pertemuan']);
        if (filled($validated['pertemuan_ke'] ?? null)) {
            $duplicateQuery->where('pertemuan_ke', $validated['pertemuan_ke']);
        }
        $currentReportId = $request->route('laporan')?->getKey();
        if ($currentReportId) {
            $duplicateQuery->whereKeyNot($currentReportId);
        }
        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'tanggal_pertemuan' => 'Laporan untuk tanggal/pertemuan ini sudah ada.',
            ]);
        }

        $validMemberIds = $this->groupMemberIds($kelompok);
        $attendance = collect($validated['anggota_hadir'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($attendance->diff($validMemberIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'anggota_hadir' => 'Anggota hadir harus berasal dari kelompok yang sedang dilaporkan.',
            ]);
        }

        $statuses = $this->extractAttendanceStatuses($request, $attendance, $validMemberIds);
        $present = collect($statuses)
            ->filter(fn ($status): bool => $status === 'hadir')
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->values();

        return [
            'tanggal_pertemuan' => $validated['tanggal_pertemuan'],
            'pertemuan_ke' => $validated['pertemuan_ke'] ?? null,
            'bahan' => $validated['bahan'],
            'ringkasan' => $validated['ringkasan'] ?? null,
            'anggota_hadir' => $present->all(),
            'jumlah_hadir' => $present->count(),
            'attendance_statuses' => $statuses,
            'catatan' => $validated['catatan'] ?? null,
            'rencana_lanjutan' => $validated['rencana_lanjutan'] ?? null,
            'kendala' => $validated['kendala'] ?? null,
        ];
    }

    private function authorizeOwnedReport(LaporanPertemuanKelompok $laporan): User
    {
        $pkk = $this->authorizeOwnedGroup($laporan->kelompok);

        abort_unless(blank($laporan->pkk_id) || (int) $laporan->pkk_id === (int) $pkk->user_id, 403);

        return $pkk;
    }

    private function authorizeOwnedGroup(?KelompokPemuridan $kelompok): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user && $user->canLeadGroups() && $kelompok && (int) $kelompok->pemimpin_id === (int) $user->user_id,
            403
        );

        return $user;
    }

    /**
     * Store all group members in the normalized table while retaining the old
     * list of present IDs on the report record.
     */
    private function syncAttendance(LaporanPertemuanKelompok $report, array $statuses, $memberIds): void
    {
        if (! Schema::hasTable('meeting_attendances')) {
            return;
        }

        $report->syncAttendanceRows($statuses, collect($memberIds)->all());
    }

    private function groupMemberIds(KelompokPemuridan $kelompok)
    {
        // The relationship foundation migration is optional during a rolling
        // deploy. Prefer historical memberships when available, then use the
        // legacy users.kelompok_id relation.
        $legacyIds = $kelompok->anggota()
            ->where('is_active', true)
            ->pluck('user_id')
            ->map(fn ($id): int => (int) $id);

        if (Schema::hasTable('group_memberships')) {
            $normalizedIds = DB::table('group_memberships')
                ->where('kelompok_id', $kelompok->kelompok_id)
                ->where('status', 'active')
                ->pluck('user_id')
                ->map(fn ($id): int => (int) $id);

            return $normalizedIds->merge($legacyIds)->unique()->values();
        }

        return $legacyIds;
    }

    private function extractAttendanceStatuses(Request $request, $presentIds, $validMemberIds): array
    {
        $valid = collect($validMemberIds)->map(fn ($id): int => (int) $id)->flip();
        $statuses = collect();

        // New forms may submit attendance[user_id] = status, or an array of
        // objects with user_id/status. Accept both shapes for API clients.
        $input = $request->input('attendance');
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                if (is_array($value)) {
                    $id = (int) ($value['user_id'] ?? $value['member_id'] ?? 0);
                    $status = (string) ($value['status'] ?? 'alpa');
                } else {
                    $id = is_numeric($key) ? (int) $key : 0;
                    $status = (string) $value;
                }

                if ($id > 0 && $valid->has($id)) {
                    $statuses->put($id, in_array($status, ['hadir', 'izin', 'sakit', 'alpa'], true) ? $status : 'alpa');
                }
            }
        }

        $explicit = $request->input('attendance_statuses');
        if (is_array($explicit)) {
            foreach ($explicit as $id => $status) {
                $id = (int) $id;
                if ($id > 0 && $valid->has($id)) {
                    $statuses->put($id, in_array($status, ['hadir', 'izin', 'sakit', 'alpa'], true) ? $status : 'alpa');
                }
            }
        }

        if ($statuses->isEmpty()) {
            foreach ($valid->keys() as $id) {
                $statuses->put((int) $id, $presentIds->contains((int) $id) ? 'hadir' : 'alpa');
            }
        } else {
            foreach ($valid->keys() as $id) {
                if (! $statuses->has((int) $id)) {
                    $statuses->put((int) $id, 'alpa');
                }
            }
        }

        return $statuses->all();
    }

    /**
     * Create one actionable task when a member is absent for at least two
     * consecutive reports. Existing open tasks are reused instead of creating
     * duplicates on every edit.
     */
    private function createAutomaticFollowUpTasks(LaporanPertemuanKelompok $report, User $owner): void
    {
        if (! Schema::hasTable('follow_up_tasks') || ! Schema::hasTable('meeting_attendances')) {
            return;
        }

        $report->loadMissing('kelompok');
        $memberIds = $this->groupMemberIds($report->kelompok);
        $recentReports = LaporanPertemuanKelompok::query()
            ->where('kelompok_id', $report->kelompok_id)
            ->orderByDesc('tanggal_pertemuan')
            ->orderByDesc('laporan_id')
            ->limit(3)
            ->get();

        foreach ($memberIds as $memberId) {
            $streak = 0;
            foreach ($recentReports as $recent) {
                $status = MeetingAttendance::query()
                    ->where('meeting_id', $recent->laporan_id)
                    ->where('user_id', $memberId)
                    ->value('status');

                if ($status === null) {
                    $status = in_array((int) $memberId, collect($recent->anggota_hadir ?? [])->map(fn ($id): int => (int) $id)->all(), true)
                        ? 'hadir'
                        : 'alpa';
                }

                if ($status === 'hadir') {
                    break;
                }
                $streak++;
            }

            if ($streak < 2) {
                continue;
            }

            $task = FollowUpTask::query()
                ->where('kelompok_id', $report->kelompok_id)
                ->where('user_id', $memberId)
                ->where('reason', 'absen_berturut')
                ->open()
                ->first();

            if ($task) {
                continue;
            }

            FollowUpTask::query()->create([
                'kelompok_id' => $report->kelompok_id,
                'laporan_id' => $report->laporan_id,
                'user_id' => $memberId,
                'assigned_to' => $owner->user_id,
                'created_by' => $owner->user_id,
                'title' => 'Hubungi anggota yang absen',
                'reason' => 'absen_berturut',
                'description' => 'Anggota tidak hadir dalam '.$streak.' pertemuan berturut-turut.',
                'due_at' => now()->addDays(7)->toDateString(),
                'status' => FollowUpTask::STATUS_OPEN,
            ]);
        }
    }
}
