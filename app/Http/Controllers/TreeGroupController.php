<?php

namespace App\Http\Controllers;

use App\Models\KelompokPemuridan;
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
        $this->authorizeManageData();

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

        $leader = User::query()->findOrFail($validated['pemimpin_id']);
        $groupName = trim((string) ($validated['nama_kelompok'] ?? ''));

        $leader->forceFill([
            'role' => 'pkk',
            'admin_tipe' => null,
        ])->save();

        KelompokPemuridan::query()->create([
            'nama_kelompok' => $groupName !== '' ? $groupName : 'Kelompok '.$leader->nama_lengkap,
            'kampus_id' => $leader->kampus_id,
            'pemimpin_id' => $leader->user_id,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Kelompok berhasil ditambahkan dengan pemimpin '.$leader->nama_lengkap.'.');
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $this->authorizeManageData();

        $context = $request->validate([
            'kelompok_id' => ['nullable', Rule::exists('kelompok_pemuridan', 'kelompok_id')],
            'kampus_id' => ['nullable', Rule::exists('kampus', 'kampus_id')],
        ], [], [
            'kelompok_id' => 'kelompok',
            'kampus_id' => 'kampus',
        ]);

        $group = filled($context['kelompok_id'] ?? null)
            ? KelompokPemuridan::query()->findOrFail($context['kelompok_id'])
            : null;

        $payload = $this->validateUserPayload($request, 'nama anggota', validateCampus: $group === null);
        $payload['role'] = 'akk';
        $payload['pkk_id'] = $group?->pemimpin_id;
        $payload['kelompok_id'] = $group?->kelompok_id;
        $payload['kampus_id'] = $group ? $group->kampus_id : ($payload['kampus_id'] ?? null);
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

    private function authorizeManageData(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdminEditor()),
            403
        );
    }
}
