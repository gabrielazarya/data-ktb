<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PemuridanUserController extends Controller
{
    public function storePkk(Request $request): RedirectResponse
    {
        return $this->store($request, 'pkk');
    }

    public function updatePkk(Request $request, User $user): RedirectResponse
    {
        return $this->update($request, $user, 'pkk');
    }

    public function destroyPkk(User $user): RedirectResponse
    {
        return $this->destroy($user, 'pkk');
    }

    public function storeAkk(Request $request): RedirectResponse
    {
        return $this->store($request, 'akk');
    }

    public function updateAkk(Request $request, User $user): RedirectResponse
    {
        return $this->update($request, $user, 'akk');
    }

    public function destroyAkk(User $user): RedirectResponse
    {
        return $this->destroy($user, 'akk');
    }

    private function store(Request $request, string $role): RedirectResponse
    {
        $this->authorizeManageData();

        $validated = $this->validateUser($request, $role);

        User::query()->create($this->payload($request, $validated, $role));

        return back()->with('success', $this->roleLabel($role).' berhasil ditambahkan.');
    }

    private function update(Request $request, User $user, string $role): RedirectResponse
    {
        $this->authorizeManageData();
        $this->ensureRole($user, $role);

        $validated = $this->validateUser($request, $role, $user);

        $user->update($this->payload($request, $validated, $role, $user));

        return back()->with('success', $this->roleLabel($role).' berhasil diperbarui.');
    }

    private function destroy(User $user, string $role): RedirectResponse
    {
        $this->authorizeManageData();
        $this->ensureRole($user, $role);

        $user->delete();

        return back()->with('success', $this->roleLabel($role).' berhasil dihapus.');
    }

    private function validateUser(Request $request, string $role, ?User $user = null): array
    {
        $passwordRules = $user
            ? ['nullable', 'string', 'min:6']
            : ['required', 'string', 'min:6'];

        $rules = [
            'username' => [
                'required',
                'string',
                'max:256',
                Rule::unique('users', 'username')->ignore($user?->user_id, 'user_id'),
            ],
            'password' => $passwordRules,
            'nama_lengkap' => ['required', 'string', 'max:256'],
            'tanggal_lahir' => ['nullable', 'date'],
            'kampus_id' => ['nullable', Rule::exists('kampus', 'kampus_id')],
            'regio_id' => ['nullable', Rule::exists('regios', 'regio_id')],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'kategori_jurusan_id' => ['nullable', Rule::exists('kategori_jurusan', 'kategori_jurusan_id')],
            'angkatan' => ['nullable', 'integer', 'between:1900,'.((int) date('Y') + 1)],
            'is_target' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($role === 'akk') {
            $rules['pkk_id'] = [
                'nullable',
                Rule::exists('users', 'user_id')->where(fn ($query) => $query->where('role', 'pkk')),
            ];
        }

        return $request->validate($rules, [], [
            'username' => 'username',
            'password' => 'password',
            'nama_lengkap' => 'nama lengkap',
            'tanggal_lahir' => 'tanggal lahir',
            'kampus_id' => 'kampus',
            'regio_id' => 'regio',
            'jurusan' => 'jurusan',
            'kategori_jurusan_id' => 'kategori jurusan',
            'angkatan' => 'angkatan',
            'pkk_id' => 'pemimpin PKK',
            'is_target' => 'target PKK',
            'is_active' => 'status aktif',
        ]);
    }

    private function payload(Request $request, array $validated, string $role, ?User $user = null): array
    {
        $payload = Arr::only($validated, [
            'username',
            'nama_lengkap',
            'tanggal_lahir',
            'kampus_id',
            'regio_id',
            'jurusan',
            'kategori_jurusan_id',
            'angkatan',
            'pkk_id',
        ]);

        foreach (['tanggal_lahir', 'kampus_id', 'regio_id', 'jurusan', 'kategori_jurusan_id', 'angkatan', 'pkk_id'] as $field) {
            if (blank($payload[$field] ?? null)) {
                $payload[$field] = null;
            }
        }

        if (filled($validated['password'] ?? null)) {
            $payload['password'] = $validated['password'];
        }

        $payload['role'] = $role;
        $payload['pkk_id'] = $role === 'akk' ? ($payload['pkk_id'] ?? null) : null;
        $payload['admin_tipe'] = null;
        $payload['is_target'] = $role === 'pkk' && $request->boolean('is_target');
        $payload['is_active'] = $request->boolean('is_active');

        return $payload;
    }

    private function ensureRole(User $user, string $role): void
    {
        abort_unless($user->role === $role, 404);
    }

    private function roleLabel(string $role): string
    {
        return strtoupper($role);
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
