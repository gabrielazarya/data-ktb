<?php

namespace App\Http\Controllers;

use App\Models\KelompokPemuridan;
use App\Models\Kampus;
use App\Models\Regio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TreeGroupController extends Controller
{
    public function storeGroup(Request $request): RedirectResponse
    {
        $actor = $this->authorizeManageData();

        $validated = $request->validate([
            'pemimpin_id' => [
                'required',
                Rule::exists('users', 'user_id')->where(fn ($query) => $query->whereIn('role', ['akk', 'pkk'])),
            ],
            'nama_kelompok' => ['nullable', 'string', 'max:256'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'pemimpin_id' => 'pemimpin kelompok',
            'nama_kelompok' => 'nama kelompok',
            'is_active' => 'status aktif',
        ]);

        $leader = User::query()
            ->with('kampus')
            ->findOrFail($validated['pemimpin_id']);
        $this->authorizePersonAccess($leader, $actor);
        $leader->loadMissing('kampus');
        $regioId = $this->resolveRegioId($actor, $leader->regio_id ?: $leader->kampus?->regio_id);
        $groupName = trim((string) ($validated['nama_kelompok'] ?? ''));

        $leader->forceFill([
            'role' => 'pkk',
            'regio_id' => $regioId,
            'admin_tipe' => null,
        ])->save();

        KelompokPemuridan::query()->create([
            'nama_kelompok' => $groupName !== '' ? $groupName : 'Kelompok '.$leader->nama_lengkap,
            'kampus_id' => $leader->kampus_id,
            'regio_id' => $regioId,
            'pemimpin_id' => $leader->user_id,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Kelompok berhasil ditambahkan dengan pemimpin '.$leader->nama_lengkap.'.');
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $actor = $this->authorizeManageData();

        $context = $request->validate([
            'kelompok_id' => ['nullable', Rule::exists('kelompok_pemuridan', 'kelompok_id')],
            'kampus_id' => ['nullable', Rule::exists('kampus', 'kampus_id')],
        ], [], [
            'kelompok_id' => 'kelompok',
            'kampus_id' => 'kampus',
        ]);

        $group = filled($context['kelompok_id'] ?? null)
            ? KelompokPemuridan::query()
                ->with(['kampus', 'pemimpin'])
                ->findOrFail($context['kelompok_id'])
            : null;
        $this->authorizeGroupAccess($group, $actor);

        $payload = $this->validateUserPayload($request, 'nama anggota', validateCampus: $group === null);
        $campus = $group === null && filled($payload['kampus_id'] ?? null)
            ? Kampus::query()->find($payload['kampus_id'])
            : null;
        $this->authorizeKampusAccess($campus, $actor);
        $payload['role'] = 'akk';
        $payload['pkk_id'] = $group?->pemimpin_id;
        $payload['kelompok_id'] = $group?->kelompok_id;
        $payload['kampus_id'] = $group ? $group->kampus_id : ($campus?->kampus_id ?? null);
        $payload['regio_id'] = $this->resolveRegioId(
            $actor,
            $group?->regio_id
            ?: $group?->kampus?->regio_id
            ?: $group?->pemimpin?->regio_id
            ?: $campus?->regio_id
        );
        $payload['admin_tipe'] = null;
        $payload['is_active'] = $request->boolean('is_active');
        $payload['username'] = $this->temporaryUsername();
        $payload['password'] = 'user';

        $member = User::query()->create($payload);
        $member->forceFill([
            'username' => $this->automaticUsername($member),
        ])->save();

        return back()->with('success', $group
            ? 'Anggota berhasil ditambahkan ke '.$group->nama_kelompok.'.'
            : 'Anggota berhasil ditambahkan ke pohon pemuridan.'
        );
    }

    private function validateUserPayload(Request $request, string $nameLabel, bool $validateCampus = true): array
    {
        $rules = [
            'nama_lengkap' => ['required', 'string', 'max:256'],
            'angkatan' => ['nullable', 'integer', 'between:1900,'.((int) date('Y') + 1)],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($validateCampus) {
            $rules['kampus_id'] = ['nullable', Rule::exists('kampus', 'kampus_id')];
        }

        $validated = $request->validate($rules, [], [
            'nama_lengkap' => $nameLabel,
            'kampus_id' => 'kampus',
            'angkatan' => 'angkatan',
            'is_active' => 'status aktif',
        ]);

        $payload = Arr::only($validated, [
            'nama_lengkap',
            'kampus_id',
            'angkatan',
        ]);

        foreach (['kampus_id', 'angkatan'] as $field) {
            if (blank($payload[$field] ?? null)) {
                $payload[$field] = null;
            }
        }

        return $payload;
    }

    private function temporaryUsername(): string
    {
        do {
            $username = 'pending_'.bin2hex(random_bytes(8));
        } while (User::query()->where('username', $username)->exists());

        return $username;
    }

    private function automaticUsername(User $user): string
    {
        $base = 'user'.$user->user_id;
        $username = $base;
        $suffix = 1;

        while (User::query()
            ->where('username', $username)
            ->whereKeyNot($user->getKey())
            ->exists()) {
            $username = $base.'_'.$suffix;
            $suffix++;
        }

        return $username;
    }

    private function resolveRegioId(User $actor, mixed $regioId = null): int
    {
        if (! $actor->isSuperAdmin()) {
            abort_unless(filled($actor->regio_id), 403);

            return (int) $actor->regio_id;
        }

        if (filled($regioId)) {
            return (int) $regioId;
        }

        return (int) Regio::query()->firstOrCreate(
            ['nama_regio' => 'Surabaya'],
            [
                'keterangan' => 'Wilayah pelayanan PMK Kota Surabaya',
                'is_active' => true,
            ]
        )->regio_id;
    }

    private function authorizePersonAccess(?User $person, User $actor): void
    {
        if ($person === null || $actor->isSuperAdmin()) {
            return;
        }

        $personRegioId = $person->regio_id ?: $person->kampus?->regio_id;

        abort_unless(filled($actor->regio_id) && (int) $personRegioId === (int) $actor->regio_id, 403);
    }

    private function authorizeGroupAccess(?KelompokPemuridan $group, User $actor): void
    {
        if ($group === null || $actor->isSuperAdmin()) {
            return;
        }

        $groupRegioId = $group->regio_id ?: $group->kampus?->regio_id ?: $group->pemimpin?->regio_id;

        abort_unless(filled($actor->regio_id) && (int) $groupRegioId === (int) $actor->regio_id, 403);
    }

    private function authorizeKampusAccess(?Kampus $kampus, User $actor): void
    {
        if ($kampus === null || $actor->isSuperAdmin()) {
            return;
        }

        abort_unless(filled($actor->regio_id) && (int) $kampus->regio_id === (int) $actor->regio_id, 403);
    }

    private function authorizeManageData(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user && $user->isAdminEditor(),
            403
        );

        return $user;
    }
}
