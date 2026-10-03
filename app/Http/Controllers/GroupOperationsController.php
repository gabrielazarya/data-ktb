<?php

namespace App\Http\Controllers;

use App\Models\Kampus;
use App\Models\KelompokPemuridan;
use App\Models\User;
use App\Services\GroupOperationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Explicit workflows for changes that affect group history.  Keeping these
 * actions separate from the CRUD controller makes it possible for the UI to
 * describe the operation (pindah, ganti pemimpin, arsip) and avoids accidental
 * data loss through a generic update/delete request.
 */
class GroupOperationsController extends Controller
{
    public function __construct(private readonly GroupOperationsService $operations) {}

    public function transferMember(Request $request, User $anggota): RedirectResponse
    {
        $actor = $this->authorizeManageData();
        $validated = $request->validate([
            'kelompok_id' => ['required', Rule::exists('kelompok_pemuridan', 'kelompok_id')],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'kelompok_id' => 'kelompok tujuan',
            'reason' => 'alasan perpindahan',
        ]);

        $anggota->loadMissing(['kampus', 'kelompokPemuridan']);
        $this->authorizePersonAccess($anggota, $actor);
        abort_if($anggota->isSuperAdmin() || $anggota->isAdmin(), 404);
        abort_unless($anggota->isLifecycleActive(), 422, 'Anggota yang dipindahkan harus berstatus aktif.');

        $group = KelompokPemuridan::query()->with(['kampus', 'pemimpin'])->findOrFail($validated['kelompok_id']);
        $this->authorizeGroupAccess($group, $actor);
        abort_unless($group->is_active, 422, 'Kelompok tujuan sudah diarsipkan.');
        abort_unless($group->pemimpin, 422, 'Kelompok tujuan belum memiliki pemimpin.');
        abort_unless($group->pemimpin->isLifecycleActive(), 422, 'Pemimpin kelompok tujuan tidak aktif.');

        $this->operations->transferMember($anggota, $group, $validated['reason'] ?? null);

        return back()->with('success', $anggota->nama_lengkap.' dipindahkan ke '.$group->nama_kelompok.'.');
    }

    public function reassignLeader(Request $request, KelompokPemuridan $kelompok): RedirectResponse
    {
        $actor = $this->authorizeManageData();
        $group = $kelompok->loadMissing(['kampus', 'pemimpin']);
        $this->authorizeGroupAccess($group, $actor);

        $validated = $request->validate([
            'pemimpin_id' => [
                'required',
                Rule::exists('users', 'user_id')->where(fn ($query) => $query->whereIn('role', ['akk', 'pkk', 'staff'])),
            ],
        ], [], ['pemimpin_id' => 'pemimpin kelompok']);

        $leader = User::query()->with('kampus')->findOrFail($validated['pemimpin_id']);
        $this->authorizePersonAccess($leader, $actor);
        abort_unless($leader->canLeadGroups(), 422, 'Anggota yang dipilih belum dapat memimpin kelompok.');
        abort_unless($leader->isLifecycleActive(), 422, 'Pemimpin yang dipilih tidak aktif.');
        abort_unless($group->is_active, 422, 'Kelompok sudah diarsipkan.');

        $this->operations->reassignLeader($group, $leader);

        return back()->with('success', 'Pemimpin '.$group->nama_kelompok.' berhasil diganti.');
    }

    public function assignCampus(Request $request, KelompokPemuridan $kelompok): RedirectResponse
    {
        $actor = $this->authorizeManageData();
        $group = $kelompok->loadMissing(['kampus', 'pemimpin']);
        $this->authorizeGroupAccess($group, $actor);

        $validated = $request->validate([
            'kampus_id' => ['required', Rule::exists('kampus', 'kampus_id')],
            'is_primary' => ['sometimes', 'boolean'],
            'assignment_type' => ['nullable', 'string', 'max:50'],
        ], [], [
            'kampus_id' => 'kampus pelayanan',
            'is_primary' => 'kampus utama',
            'assignment_type' => 'jenis penugasan',
        ]);

        $campus = Kampus::query()->findOrFail($validated['kampus_id']);
        $this->authorizeKampusAccess($campus, $actor);
        abort_unless($group->is_active, 422, 'Kelompok sudah diarsipkan.');

        $this->operations->assignCampus(
            $group,
            $campus->kampus_id,
            (bool) ($validated['is_primary'] ?? false),
            $validated['assignment_type'] ?? null,
        );

        return back()->with('success', 'Kampus '.$campus->nama_kampus.' ditambahkan ke cakupan kelompok.');
    }

    public function archiveGroup(Request $request, KelompokPemuridan $kelompok): RedirectResponse
    {
        $actor = $this->authorizeManageData();
        $group = $kelompok->loadMissing(['kampus', 'pemimpin']);
        $this->authorizeGroupAccess($group, $actor);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [], ['reason' => 'alasan pengarsipan']);

        $this->operations->archiveGroup($group, $validated['reason'] ?? null);

        return back()->with('success', 'Kelompok '.$group->nama_kelompok.' berhasil diarsipkan.');
    }

    public function archiveMember(Request $request, User $anggota): RedirectResponse
    {
        $actor = $this->authorizeManageData();
        $anggota->loadMissing(['kampus', 'kelompokPemuridan']);
        $this->authorizePersonAccess($anggota, $actor);
        abort_if(in_array($anggota->role, ['admin', 'super_admin'], true), 404);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [], ['reason' => 'alasan pengarsipan']);

        $this->operations->archiveMember($anggota, $validated['reason'] ?? null);

        return back()->with('success', 'Anggota '.$anggota->nama_lengkap.' berhasil diarsipkan.');
    }

    public function changeMemberLifecycle(Request $request, User $anggota): RedirectResponse
    {
        $actor = $this->authorizeManageData();
        $anggota->loadMissing(['kampus', 'kelompokPemuridan']);
        $this->authorizePersonAccess($anggota, $actor);
        abort_if(in_array($anggota->role, ['admin', 'super_admin'], true), 404);

        $validated = $request->validate([
            'lifecycle_status' => ['required', Rule::in(['prospek', 'active', 'cuti', 'lulus', 'pindah', 'nonaktif'])],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'lifecycle_status' => 'status anggota',
            'reason' => 'alasan perubahan status',
        ]);

        $this->operations->changeMemberLifecycle($anggota, $validated['lifecycle_status'], $validated['reason'] ?? null);

        return back()->with('success', 'Status '.$anggota->nama_lengkap.' berhasil diperbarui.');
    }

    private function authorizeManageData(): User
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->isAdminEditor(), 403);

        return $user;
    }

    private function authorizePersonAccess(?User $person, User $actor): void
    {
        if (! $person || $actor->isSuperAdmin()) {
            return;
        }

        $personRegioId = $person->regio_id ?: $person->kampus?->regio_id;
        abort_unless(filled($actor->regio_id) && (int) $personRegioId === (int) $actor->regio_id, 403);
    }

    private function authorizeGroupAccess(?KelompokPemuridan $group, User $actor): void
    {
        if (! $group || $actor->isSuperAdmin()) {
            return;
        }

        $groupRegioId = $group->regio_id ?: $group->kampus?->regio_id ?: $group->pemimpin?->regio_id;
        abort_unless(filled($actor->regio_id) && (int) $groupRegioId === (int) $actor->regio_id, 403);
    }

    private function authorizeKampusAccess(?Kampus $campus, User $actor): void
    {
        if (! $campus || $actor->isSuperAdmin()) {
            return;
        }

        abort_unless(filled($actor->regio_id) && (int) $campus->regio_id === (int) $actor->regio_id, 403);
    }
}
