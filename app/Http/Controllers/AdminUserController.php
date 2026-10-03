<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManageData();

        $user = User::query()->create(array_merge($this->payload($this->validateUser($request)), [
            'username' => $this->temporaryUsername(),
            'password' => 'admin',
            'is_active' => true,
            'must_change_password' => true,
        ]));

        $user->forceFill([
            'username' => $this->automaticUsername($user),
        ])->save();
        AuditLog::record('admin.created', $user);

        return back()->with('success', 'Pengguna admin berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManageData();
        $this->authorizeAdminUser($user);

        $user->update($this->payload($this->validateUser($request)));
        AuditLog::record('admin.updated', $user);

        return back()->with('success', 'Pengguna admin berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeManageData();
        $this->authorizeAdminUser($user);

        abort_if(Auth::id() === $user->getKey(), 422, 'Akun yang sedang digunakan tidak bisa dihapus.');

        $user->delete();
        AuditLog::record('admin.deleted', null, ['deleted_user_id' => $user->user_id]);

        return back()->with('success', 'Pengguna admin berhasil dihapus.');
    }

    private function validateUser(Request $request): array
    {
        return $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:256'],
            'regio_id' => ['required', 'integer', Rule::exists('regios', 'regio_id')],
            'admin_tipe' => ['required', Rule::in(['pelihat', 'editor'])],
        ], [], [
            'nama_lengkap' => 'nama lengkap',
            'regio_id' => 'regio',
            'admin_tipe' => 'tipe admin',
        ]);
    }

    private function payload(array $validated): array
    {
        return [
            'nama_lengkap' => $validated['nama_lengkap'],
            'regio_id' => $validated['regio_id'],
            'role' => 'admin',
            'admin_tipe' => $validated['admin_tipe'],
            'kampus_id' => null,
            'angkatan' => null,
            'pkk_id' => null,
            'kelompok_id' => null,
        ];
    }

    private function temporaryUsername(): string
    {
        do {
            $username = 'pending_admin_'.bin2hex(random_bytes(8));
        } while (User::query()->where('username', $username)->exists());

        return $username;
    }

    private function automaticUsername(User $user): string
    {
        $base = 'admin'.$user->user_id;
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

    private function authorizeAdminUser(User $user): void
    {
        abort_unless($user->role === 'admin', 404);
    }

    private function authorizeManageData(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user && $user->isSuperAdmin(),
            403
        );
    }
}
